<?php

namespace SilverStripe\Cow\Commands;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class Command extends Console\Command\Command
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription($this->description);

		array_map(function($argument) {
			call_user_func_array([$this, "addArgument"], $argument);
		}, $this->getArguments());

		array_map(function($option) {
			call_user_func_array([$this, "addOption"], $option);
		}, $this->getOptions());
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->fire();
    }

    /**
     * Defers to the subclass functionality.
     */
    abstract protected function fire();

	/**
	 * Error if version is invalid
	 * 
	 * @param type $version
	 * @throws InvalidArgumentException
	 */
	protected function validateVersion($version) {
		if(!preg_match('/^(\d+)\.(\d+)\.(\d+)(\-(rc|alpha|beta)\d*)?$/', $version)) {
			throw new InvalidArgumentException("Invalid version $version. Expect full version (3.1.13) with optional rc|alpha|beta suffix");
		}
	}
	
	/**
	 * Ask a question from the user
	 * 
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param type $text
	 */
	protected function ask($text) {
		$helper = $this->getHelper('question');
		$question = new Question($text);
		return $helper->ask($this->input, $this->output, $question);
	}
	
	protected $fromVersion = null;
	
	/**
	 * Get the from version to release from
	 * 
	 * @return string
	 */
	public function getFromVersion() {
		if($this->fromVersion) {
			return $this->fromVersion;
		}
		$this->fromVersion = $this->ask("<question>Last tag to build changelog from?: </question>");
		$this->validateVersion($this->fromVersion);
		return $this->fromVersion;
	}
	
	protected $toVersion = null;
	
	/**
	 * Get the to version to release
	 * 
	 * @return string
	 */
	public function getToVersion() {
		if($this->toVersion) {
			return $this->toVersion;
		}
		$this->toVersion = $this->ask("<question>Last tag to build changelog from?: </question>");
		$this->validateVersion($this->toVersion);
		return $this->toVersion;
	}

	/**
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			// "argument", InputArgument::REQUIRED, "argument description",
		];
	}

	/**
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			// "option", "o", InputOption::VALUE_REQUIRED, "option description"
		];
	}

	/**
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
     */
    protected function getArgument($name, $default = null)
	{
		$argument = $this->input->getArgument($name);

		if (empty($argument)) {
			return $default;
		}

		return $argument;
	}

	/**
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
     */
    protected function getOption($name, $default = null)
	{
		$option = $this->input->getOption($name);

		if (empty($option)) {
			return $default;
		}

		return $option;
	}

	/**
	 * @param string $command
	 *
	 * @return string
     */
    protected function exec($command)
	{
		exec($command, $output, $code);

		if ($code !== 0) {
			throw new RuntimeException("Command unsuccessful");
		}

		return $output;
	}
}
