<?php

namespace SilverStripe\Cow\Steps;

use SilverStripe\Cow\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Step {
	
	/**
	 * @var Command
	 */
	protected $command;
	
	public function __construct(Command $command) {
		$this->command = $command;
	}
	abstract public function run(InputInterface $input, OutputInterface $output);
}
