<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Steps\Release\CreateChangeLog;

/**
 * Description of Create
 *
 * @author dmooyman
 */
class ChangeLog extends Release {
	
	/**
	 *
	 * @var string
	 */
	protected $name = 'release:changelog';
	
	protected $description = 'Generate changelog';
	
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
