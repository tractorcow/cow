<?php

namespace SilverStripe\Cow\Commands\Project;

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
			->addArgument('version', InputArgument::REQUIRED, 'Composer constraint to create this project with')
			->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Optional directory to create this project in')
			->addOption('security', 's', InputOption::VALUE_NONE, 'Update git remotes to point to security project');
	}
	
	
	protected function fire() {
		$version = $this->input->getArgument('version');
		$directory = $this->input->getOption('directory');
		
		if($this->input->getOption('security')) {
			throw new InvalidArgumentException('--security flag not yet implemented');
		}
		
		$step = new CreateProject($this, $version, $directory);
		$step->run($this->input, $this->output);
	}

}
