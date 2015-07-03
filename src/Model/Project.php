<?php

namespace SilverStripe\Cow\Model;

/**
 * Represents information about a project in a given directory
 * 
 * Is also the 'silverstripe-installer' module
 */
class Project extends Module {
	
	public function __construct($directory) {
		parent::__construct($directory, 'installer');
	}
	
	/**
	 * Gets the list of modules in this installer
	 */
	public function getModules() {
		// Include self as head module
		$modules = array($this);
		
		// Search all directories
		foreach(glob($this->directory."/*", GLOB_ONLYDIR) as $dir) {
			if($this->isModulePath($dir)) {
				$name = basename($dir);
				$modules[] = new Module($dir, $name, $this);
			}
		}
		return $modules;
	}

	/**
	 * Get a module by name
	 *
	 * @param string $name
	 * @return Module
	 */
	public function getModule($name) {
		$dir = $this->directory . DIRECTORY_SEPARATOR . $name;
		if($this->isModulePath($dir)) {
			return new Module($dir, $name, $this);
		}
	}

	/**
	 * Check if the given path contains a module
	 *
	 * @return bool
	 */
	protected function isModulePath($path) {
		// Check for _config
		if(!is_file("$path/_config.php") && !is_dir("$path/_config")) {
			return false;
		}

		// Skip ignored modules
		$name = basename($path);
		$ignore = array('mysite', 'assets', 'vendor');
		return !in_array($name, $ignore);
	}
}
