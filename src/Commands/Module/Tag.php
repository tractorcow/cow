<?php

namespace SilverStripe\Cow\Commands\Module;

use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\ReleaseVersion;
use SilverStripe\Cow\Steps\Module\TagAnnotatedModule;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Does an annoted module tag and realease.
 */
class Tag extends Command
{
    protected $name = 'module:tag';

    protected $description = 'Tag a module and push to github with annotated changelog';

    protected function configureOptions()
    {
        $this->addArgument('module', InputArgument::REQUIRED, 'Module name to release');
        $this->addArgument('version', InputArgument::REQUIRED, 'Version tag');
        $this->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'Version to generate changelog from');
        $this->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Project root directory');
    }

    /**
     * Defers to the subclass functionality.
     */
    protected function fire()
    {
        $version = $this->getInputVersion();
        $module = $this->getInputModule();
        $directory = $this->getInputDirectory();
        $fromVersion = $this->getInputFromVersion($version);

        $step = new TagAnnotatedModule($this, $version, $fromVersion, $directory, $module);
        $step->run($this->input, $this->output);
    }


    /**
     * Determine the 'from' version for generating changelogs
     *
     * @param ReleaseVersion $version
     * @return ReleaseVersion
     */
    protected function getInputFromVersion(ReleaseVersion $version)
    {
        $value = $this->input->getOption('from');
        if ($value) {
            return new ReleaseVersion($value);
        } else {
            return $version->getPriorVersion();
        }
    }


    /**
     * Get the version to release
     *
     * @return ReleaseVersion
     */
    protected function getInputVersion()
    {
        // Version
        $value = $this->input->getArgument('version');
        return new ReleaseVersion($value);
    }

    /**
     * Get the directory the project is, or will be in
     *
     * @return string
     */
    protected function getInputDirectory()
    {
        $directory = $this->input->getOption('directory');
        if (!$directory) {
            $directory = getcwd();
        }
        return $directory;
    }

    /**
     * Module name to tag
     *
     * @return string
     */
    protected function getInputModule()
    {
        return $this->input->getArgument('module');
    }
}
