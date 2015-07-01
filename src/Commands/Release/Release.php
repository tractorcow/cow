<?php

namespace SilverStripe\Cow\Commands\Release;

use InvalidArgumentException;
use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Steps\Composer\CreateProject;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
			->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Optional directory to release project from');
	}
	
	
	protected function fire() {
		$version = new ReleaseVersion($this->input->getArgument('version'));

		$directory = $this->input->getOption('directory');
		if(!$directory) {
			$directory = $this->pickDirectory($version);
		}

		// todo - stuff
		
		
	}

}
