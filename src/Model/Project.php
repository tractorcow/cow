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
		$ignore = array('mysite', 'assets', 'vendor');
		
		// Include self as head module
		$modules = array($this);
		
		// Search all directories
		foreach(glob($this->directory."/*", GLOB_ONLYDIR) as $dir) {
			// Check for _config
			if(!is_file("$dir/_config.php") && !is_dir("$dir/_config")) {
				continue;
			}
			$name = basename($dir);
			
			// Skip ignored modules
			if(!in_array($name, $ignore)) {
				$modules[] = new Module($dir, $name, $this);
			}
		}
		return $modules;
	}
}
