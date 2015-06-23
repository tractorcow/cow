<?php

namespace SilverStripe\Cow\Steps\Composer;

use SilverStripe\Cow\Steps\Step;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates a new project
 */
class CreateProject extends Step {
	
	protected $package = 'silverstripe/installer';
	
	protected $stability = 'dev';
	
	protected $version;
	
	protected $directory;
	
	public function __construct($version, $directory = '.') {
		$this->version = $version;
		$this->directory = $directory ?: '.';
	}
	
	public function run(InputInterface $input, OutputInterface $output) {
		passthru("composer create-project --prefer-source --keep-vcs {$this->package} {$this->directory} {$this->version}");
	}
}
