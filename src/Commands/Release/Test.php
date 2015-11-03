<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Steps\Release\RunTests;

/**
 * Run tests on this release
 *
 * @author dmooyman
 */
class Test extends Release
{
    protected $name = 'release:test';

    protected $description = 'Test this release';

    protected function fire()
    {
        // Get arguments
        $version = $this->getInputVersion();
        $directory = $this->getInputDirectory($version);

        // Steps
        $step = new RunTests($this, $directory);
        $step->run($this->input, $this->output);
    }
}
