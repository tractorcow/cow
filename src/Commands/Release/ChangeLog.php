<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Steps\Release\CreateChangeLog;

/**
 * Description of Create
 *
 * @author dmooyman
 */
class ChangeLog extends Release
{
    /**
     *
     * @var string
     */
    protected $name = 'release:changelog';
    
    protected $description = 'Generate changelog';
    
    protected function fire()
    {
        // Get arguments
        $version = $this->getInputVersion();
        $fromVersion = $this->getInputFromVersion($version);
        $directory = $this->getInputDirectory($version);
        $modules = $this->getReleaseModules($directory);

        // Steps
        $step = new CreateChangeLog($this, $version, $fromVersion, $directory, $modules);
        $step->run($this->input, $this->output);
    }
}
