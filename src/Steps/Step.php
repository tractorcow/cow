<?php

namespace SilverStripe\Cow\Steps;

use SilverStripe\Cow\Commands\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

abstract class Step {
	
	/**
	 * @var Command
	 */
	protected $command;
	
	public function __construct(Command $command) {
		$this->command = $command;
	}

	abstract public function run(InputInterface $input, OutputInterface $output);

	/**
	 *
	 * @return ProcessHelper
	 */
	protected function getProcessHelper() {
		return  $this->command->getHelper('process');
	}

	/**
	 * Run an arbitrary command
	 *
	 * To display errors/output make sure to run with -vvv
	 *
	 * @param OutputInterface $output
	 * @param string|array|Process $command An instance of Process or an array of arguments to escape and run or a command to run
	 * @param string|null $error An error message that must be displayed if something went wrong
	 * @return string|bool Output, or false if error
	 */
	protected function runCommand(OutputInterface $output, $command, $error = null) {
		$helper = $this->getProcessHelper();

		// Prepare unbound command
		if (is_array($command)) {
            $process = ProcessBuilder::create($command)->getProcess();
        } elseif ($command instanceof Process) {
            $process = $command;
        } else {
            $process = new Process($command);
        }

		// Run it
		$process->setTimeout(null);
		$result = $helper->run($output, $process, $error);

		// And cleanup
		if($result->isSuccessful()) {
			return $result->getOutput();
		} else {
			return false;
		}
	}
}
