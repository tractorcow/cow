<?php

namespace SilverStripe\Cow\Steps\Release;

use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\Project;
use SilverStripe\Cow\Steps\Step;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Creates a new changelog
 */
class CreateChangeLog extends Step {
	
	protected $version;
	
	protected $directory;
	
	public function __construct(Command $command, $version, $directory = '.') {
		parent::__construct($command);
		
		$this->version = $version;
		$this->directory = $directory ?: '.';
	}
	
	public function run(InputInterface $input, OutputInterface $output) {
		// Check statistics of the current project
		$project = new Project($this->directory);
		
		// Check branch
		$branch = $project->getBranch();
		$fromVersion = $this->command->getFromVersion();
		$toVersion = $this->command->getToVersion();
		
		// Todo - make the changelog
		var_dump($branch);
		var_dump($fromVersion);
		var_dump($toVersion);
	}
}
