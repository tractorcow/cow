<?php

namespace SilverStripe\Cow\Steps\Release;

use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\Project;
use SilverStripe\Cow\Steps\Step;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Push up changes to github for each module (including tags)
 *
 * @author dmooyman
 */
class PushRelease extends Step {

	/**
	 * @var Project
	 */
	protected $project;

	/**
	 * Create branch step
	 *
	 * @param Command $command
	 * @param string $directory Where to translate
	 * @param string|null $branch Branch name, if necessary
	 */
	public function __construct(Command $command, $directory) {
		parent::__construct($command);

		$this->project = new Project($directory);
	}

	/**
	 * @return Project
	 */
	public function getProject() {
		return $this->project;
	}

	public function getStepName() {
		return 'push';
	}

	public function run(InputInterface $input, OutputInterface $output) {
		$this->log($output, "Pushing all modules to origin");
		$modules = $this->getProject()->getModules();
		foreach($modules as $module) {
			$this->log($output, "Pushing module <info>" . $module->getName() . "</info>");
			$module->pushTo('origin', true);
		}
		$this->log($output, 'Branching complete');
	}
}
