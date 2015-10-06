<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Steps\Release\TagModules;

/**
 * Create a new branch
 *
 * @author dmooyman
 */
class Tag extends Publish {

	/**
	 * @var string
	 */
	protected $name = 'release:tag';

	protected $description = 'Tag all modules';

	protected function fire() {
		// Get arguments
		$version = $this->getInputVersion();
		$directory = $this->getInputDirectory($version);
		$modules = $this->getReleaseModules($directory);

		// Steps
		$step = new TagModules($this, $version, $directory, $modules);
		$step->run($this->input, $this->output);
	}
}
