<?php

namespace SilverStripe\Cow\Model;

use InvalidArgumentException;

/**
 * Represents information about a project in a given directory
 *
 * Is also the 'silverstripe-installer' module
 */
class Project extends Module
{
    public function __construct($directory)
    {
        parent::__construct($directory, 'installer');

        if (!self::existsIn($this->directory)) {
            throw new InvalidArgumentException("No installer found in \"{$this->directory}\"");
        }
    }

    /**
     * Is there a project in the given directory?
     *
     * @param string $directory
     * @return bool
     */
    public static function existsIn($directory)
    {
        return file_exists($directory . '/mysite');
    }

    /**
     * Gets the list of self.version modules in this installer
     *
     * @param array $filter Optional list of modules to filter
     * @param bool $listIsExclusive Set to true if this list is exclusive
     * @param string $versionConstraint root composer.json version constraint.
     * Ignored if $listIsExclusive is set to true and $filter contains modules.
     * @return Module[]
     */
    public function getModules($filter = array(), $listIsExclusive = false, $versionConstraint = 'self.version')
    {
        $composer = $this->getComposerData();

        // Include self as head module
        $modules = array();
        if (empty($filter) || in_array($this->getName(), $filter) != $listIsExclusive) {
            $modules[] = $this;
        }

        // Search all directories
        foreach (glob($this->directory."/*", GLOB_ONLYDIR) as $dir) {
            // Skip non-modules
            if (!$this->isModulePath($dir)) {
                continue;
            }

            // Skip if filtered
            $name = basename($dir);
            if (!empty($filter) && (in_array($name, $filter) == $listIsExclusive)) {
                continue;
            }
            $module = new Module($dir, $name, $this);

            // Filter by $versionConstraint module,
            // but let whitelisted queries to override this
            if ($versionConstraint && (empty($filter) || $listIsExclusive)) {
                $composerName = $module->getComposerName();
                if (!isset($composer['require'][$composerName]) ||
                    ($composer['require'][$composerName] !== $versionConstraint)
                ) {
                    continue;
                }
            }

            // Save
            $modules[] = $module;
        }
        return $modules;
    }

    /**
     * Get a module by name
     *
     * @param string $name
     * @return Module
     */
    public function getModule($name)
    {
        $dir = $this->directory . DIRECTORY_SEPARATOR . $name;
        if ($this->isModulePath($dir)) {
            return new Module($dir, $name, $this);
        }
    }

    /**
     * Check if the given path contains a non-installer module
     *
     * @return bool
     */
    protected function isModulePath($path)
    {
        // Check for _config
        if (!is_file("$path/_config.php") && !is_dir("$path/_config")) {
            return false;
        }

        // Skip ignored modules
        $name = basename($path);
        $ignore = array('mysite', 'assets', 'vendor');
        return !in_array($name, $ignore);
    }

    public function getMainDirectory()
    {
        // Look in mysite for main content
        return $this->getDirectory() . '/mysite';
    }
}
