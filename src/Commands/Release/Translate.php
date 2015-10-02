<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Steps\Release\UpdateTranslations;

/**
 * Description of Create
 *
 * @author dmooyman
 */
class Translate extends Release {
	
	protected $name = 'release:translate';

	protected $description = 'Translate this release';
	
	protected function fire() {
		// Get arguments
		$version = $this->getInputVersion();
		$directory = $this->getInputDirectory($version);
		$modules = $this->getReleaseModules($directory);

		// Steps
		$step = new UpdateTranslations($this, $directory, $modules);
		$step->run($this->input, $this->output);
	}

}
