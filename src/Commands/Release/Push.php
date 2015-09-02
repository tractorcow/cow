<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Steps\Release\PushRelease;

/**
 * Create a new branch
 *
 * @author dmooyman
 */
class Push extends Release {

	/**
	 *
	 * @var string
	 */
	protected $name = 'release:push';

	protected $description = 'Push up changes to origin (including tags)';

	protected function fire() {
		// Get arguments
		$version = $this->getInputVersion();
		$directory = $this->getInputDirectory($version);

		// Steps
		$step = new PushRelease($this, $directory);
		$step->run($this->input, $this->output);
	}
}
