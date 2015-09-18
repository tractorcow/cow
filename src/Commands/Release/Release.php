<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\ReleaseVersion;
use SilverStripe\Cow\Steps\Release\CreateBranch;
use SilverStripe\Cow\Steps\Release\CreateChangeLog;
use SilverStripe\Cow\Steps\Release\CreateProject;
//use SilverStripe\Cow\Steps\Release\PushRelease;
//use SilverStripe\Cow\Steps\Release\TagModules;
use SilverStripe\Cow\Steps\Release\RunTests;
use SilverStripe\Cow\Steps\Release\UpdateTranslations;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\InvalidArgumentException;

/**
 * Description of Create
 *
 * @author dmooyman
 */
class Release extends Command {
	
	protected $name = 'release';
	
	protected $description = 'Execute each release step in order to publish a new version';

	const BRANCH_AUTO = 'auto';
	
	protected function configure() {
		parent::configure();
		
		$this
			->addArgument('version', InputArgument::REQUIRED, 'Exact version tag to release this project as')
			->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'Version to generate changelog from')
			->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Optional directory to release project from')
			->addOption('security', 's', InputOption::VALUE_NONE, 'Update git remotes to point to security project')
			->addOption('branch', 'b', InputOption::VALUE_REQUIRED, 'Branch each module to this')
			->addOption('branch-auto', 'a', InputOption::VALUE_NONE, 'Automatically branch to major.minor.patch')
			->addOption('aws-profile', null, InputOption::VALUE_REQUIRED, "AWS profile to use for upload", "silverstripe");
	}
	
	
	protected function fire() {
		// Get arguments
		$version = $this->getInputVersion();
		$fromVersion = $this->getInputFromVersion($version);
		$directory = $this->getInputDirectory($version);
		$branch = $this->getInputBranch($version);
		$awsProfile = $this->getInputAWSProfile();

		// Make the directory
		$project = new CreateProject($this, $version, $directory);
		$project->run($this->input, $this->output);

		// Change to the correct temp branch (if given)
		$branch = new CreateBranch($this, $directory, $branch);
		$branch->run($this->input, $this->output);
		
		// Update all translations
		$translate = new UpdateTranslations($this, $directory);
		$translate->run($this->input, $this->output);
		
		// Run tests
		$test = new RunTests($this, $directory);
		$test->run($this->input, $this->output);

		// Generate changelog
		$changelogs = new CreateChangeLog($this, $version, $fromVersion, $directory);
		$changelogs->run($this->input, $this->output);
		
		// Tag
		/* new TagModules($this, $version, $directory), */ // Run this manually for now via release:tag
		// Push tag & branch
		/* new PushRelease($this, $directory) */ // Run this manually for now via release:push
		// Create packages
		// @todo
		// Upload
		/* new UploadArchive($this, $version, $directory, $awsProfile) */ // Run this manually for now via release:upload
		// Merge temp branch back into main
		// @todo (or just do manually? maybe output some manual instructions here)
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
	 * Get the aws profile to use
	 *
	 * @return silverstripe
	 */
	public function getInputAWSProfile() {
		return $this->input->getOption('aws-profile');
	}

	/**
	 * Determine the branch name that should be used
	 *
	 * @param ReleaseVersion $version
	 * @return string|null
	 */
	protected function getInputBranch(ReleaseVersion $version) {
		$branch = $this->input->getOption('branch');
		if($branch) {
			return $branch;
		}

		// If not explicitly specified, automatically select
		if($this->input->getOption('branch-auto')) {
			return $version->getValueStable();
		}
		return null;
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
