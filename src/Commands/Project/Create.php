<?php

namespace SilverStripe\Cow\Commands\Project;

use InvalidArgumentException;
use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\ReleaseVersion;
use SilverStripe\Cow\Steps\Composer\CreateProject;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Description of Create
 *
 * @author dmooyman
 */
class Create extends Command {
	
	/**
	 *
	 * @var string
	 */
	protected $name = 'project:create';
	
	protected $description = 'Setup a new release';
	
	protected function configure() {
		parent::configure();
		
		$this
			->addArgument('version', InputArgument::REQUIRED, 'Exact version tag to release this project as')
			->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Optional directory to create this project in')
			->addOption('security', 's', InputOption::VALUE_NONE, 'Update git remotes to point to security project');
	}
	
	
	protected function fire() {
		$version = new ReleaseVersion($this->input->getArgument('version'));
		
		$directory = $this->input->getOption('directory');
		if(!$directory) {
			$directory = $this->pickDirectory($version);
		}

		$security = $this->input->getOption('security');
		if($security) {
			throw new InvalidArgumentException('--security flag not yet implemented');
		}

		// Steps
		$step = new CreateProject($this, $version, $directory);
		$step->run($this->input, $this->output);
	}

}
