<?php

namespace SilverStripe\Cow;

use SilverStripe\Cow\Commands\MooCommand;
use Symfony\Component\Console\Application;

class CowApp extends Application {
	public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN') {
		parent::__construct($name, $version);

		// Add all commands
		$this->add(new MooCommand());
	}
}
