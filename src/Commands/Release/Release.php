<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\ReleaseVersion;
use SilverStripe\Cow\Steps\Release\CreateChangeLog;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\InvalidArgumentException;

/**
 * Description of Create
 *
 * @author dmooyman
 */
class Release extends Command {
	
	/**
	 *
	 * @var string
	 */
	protected $name = 'release';
	
	protected $description = 'Execute each release step in order to publish a new version';
	
	protected function configure() {
		parent::configure();
		
		$this
			->addArgument('version', InputArgument::REQUIRED, 'Exact version tag to release this project as')
			->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'Version to generate changelog from')
			->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Optional directory to release project from')
			->addOption('security', 's', InputOption::VALUE_NONE, 'Update git remotes to point to security project');
	}
	
	
	protected function fire() {
		// Get arguments
		$version = $this->getInputVersion();
		$fromVersion = $this->getInputFromVersion($version);
		$directory = $this->getInputDirectory($version);

		// Steps
		$step = new CreateChangeLog($this, $version, $fromVersion, $directory);
		$step->run($this->input, $this->output);
	}

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

	/**
	 * Determine if the release selected is a security one
	 *
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	protected function getInputSecurity() {
		$security = $this->input->getOption('security');
		if($security) {
			throw new InvalidArgumentException('--security flag not yet implemented');
		}
		return (bool)$security;
	}

}
