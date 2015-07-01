<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Model\ReleaseVersion;
use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Steps\Release\CreateChangeLog;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Description of Create
 *
 * @author dmooyman
 */
class ChangeLog extends Command {
	
	/**
	 *
	 * @var string
	 */
	protected $name = 'release:changelog';
	
	protected $description = 'Generate changelog';
	
	protected function configure() {
		parent::configure();
		
		$this
			->addArgument('version', InputArgument::REQUIRED, 'Exact version tag to release this project as')
			->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'Version to generate changelog from')
			->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Optional directory to release project from');
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

}
