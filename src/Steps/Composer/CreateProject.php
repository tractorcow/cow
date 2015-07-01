<?php

namespace SilverStripe\Cow\Steps\Composer;

use SilverStripe\Cow\Model\ReleaseVersion;
use SilverStripe\Cow\Steps\Step;
use SilverStripe\Cow\Commands\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates a new project
 */
class CreateProject extends Step {
	
	protected $package = 'silverstripe/installer';
	
	protected $stability = 'dev';

	/**
	 * @var ReleaseVersion
	 */
	protected $version;

	/**
	 * @var string
	 */
	protected $directory;

	/**
	 *
	 * @param Command $command
	 * @param array $version
	 * @param type $directory
	 */
	public function __construct(Command $command, ReleaseVersion $version, $directory = '.') {
		parent::__construct($command);
		
		$this->version = $version;
		$this->directory = $directory ?: '.';
	}


	/**
	 * Create a new project
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	public function run(InputInterface $input, OutputInterface $output) {
		// Pick and install this version
		$version = $this->getBestVersion($output);
		$this->installVersion($output, $version);
	}

	/**
	 * Determine installable versions composer knows about and can install
	 *
	 * @return array
	 */
	protected function getAvailableVersions(OutputInterface $output) {
        $output = $this->runCommand($output, array("composer", "show", $this->package));

		// Parse output
		if($output && preg_match('/^versions\s*:\s*(?<versions>(\S.+\S))\s*$/m', $output, $matches)) {
			return preg_split('/\s*,\s*/', $matches['versions']);
		}

		throw new Exception("Could not parse available versions from command \"composer show {$this->package}\"");
	}

	/**
	 * Install a given version
	 *
	 * @param OutputInterface $output
	 * @param string $version
	 */
	protected function installVersion(OutputInterface $output, $version) {
		$output->writeln("<info>Installing version {$version} in {$this->directory}</info>");
		$command = array(
			"composer", "create-project", "--prefer-source", "--keep-vcs", $this->package, $this->directory, $version
		);
		$result = $this->runCommand($output, $command);
		if($result === false) {
			throw new \Exception("Could not create project with version {$version}");
		}
	}

	/**
	 * Get best version to install
	 *
	 * @param OutputInterface $output
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	protected function getBestVersion(OutputInterface $output) {
		$output->writeln('<info>Determining best version to install</info>');
		
		// Determine best version to install
		$available = $this->getAvailableVersions($output);
		$versions = $this->version->getComposerVersions();

		// Choose based on available and preference
		foreach($versions as $version) {
			if(in_array($version, $available)) {
				return $version;
			}
		}

		throw new \InvalidArgumentException("Could not install project from version ".$this->version->getValue());
	}
}
