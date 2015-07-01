<?php

namespace SilverStripe\Cow\Commands;

use SilverStripe\Cow\Model\ReleaseVersion;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
	 * Get the version to release
	 * 
	 * @return ReleaseVersion
	 */
	protected function getInputVersion() {
		// Version
		$value = $this->input->getArgument('version');
		return new ReleaseVersion($value);
	}
	
	/**
	 * Determine the 'from' version for generating changelogs
	 * 
	 * @param ReleaseVersion $version
	 */
	protected function getInputFromVersion(ReleaseVersion $version) {
		$value = $this->input->getOption('from');
		if($value) {
			return new ReleaseVersion($value);
		} else {
			return $version->getPriorVersion();
		}
	}
	
	/**
	 * Get the directory the project is, or will be in
	 * 
	 * @param ReleaseVersion $version
	 * @return string
	 */
	protected function getInputDirectory(ReleaseVersion $version) {
		$directory = $this->input->getOption('directory');
		if(!$directory) {
			$directory = $this->pickDirectory($version);
		}
		return $directory;
	}

	/**
	 * Guess a directory to install/read the given version
	 *
	 * @param ReleaseVersion $version
	 * @return string
	 */
	protected function pickDirectory(ReleaseVersion $version) {
		$filename = DIRECTORY_SEPARATOR . 'release-' . $version->getValue();
		$cwd = getcwd();
		
		// Check if we are already in this directory
		if(strrpos($cwd, $filename) === strlen($cwd) - strlen($filename)) {
			return $cwd;
		}
		
		return $cwd . $filename;
	}
}
