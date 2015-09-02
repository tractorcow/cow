<?php

namespace SilverStripe\Cow\Steps\Release;

use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\Project;
use SilverStripe\Cow\Model\ReleaseVersion;
use SilverStripe\Cow\Steps\Step;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



/**
 * Tag all modules
 *
 * @author dmooyman
 */
class TagModules extends Step {

	/**
	 * @var ReleaseVersion
	 */
	protected $version;

	/**
	 *
	 * @var Project
	 */
	protected $project;

	public function __construct(Command $command, ReleaseVersion $version, $directory = '.') {
		parent::__construct($command);

		$this->version = $version;
		$this->project = new Project($directory);
	}

	/**
	 *
	 * @return ReleaseVersion
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @return Project
	 */
	public function getProject() {
		return $this->project;
	}

	public function run(InputInterface $input, OutputInterface $output) {
		$this->log($output, "Tagging modules as " . $this->getVersion()->getValue());

		$modules = $this->getProject()->getModules();
		foreach($modules as $module) {
			$this->log($output, "Tagging module " . $module->getName());
			$module->addTag($this->getVersion()->getValue());
		}
		
		$this->log($output, 'Tagging complete');
	}


	public function getStepName() {
		return 'tag';
	}
}
