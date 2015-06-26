<?php

namespace SilverStripe\Cow\Model;

use Gitonomy\Git\Reference\Branch;
use Gitonomy\Git\Repository;
use InvalidArgumentException;

/**
 * Represents information about a project in a given directory
 */
class Project {
	
	protected $directory;
	
	public function __construct($directory) {
		$this->directory = realpath($directory);
		
		if(!$this->isValid()) {
			throw new InvalidArgumentException("No project in directory \"".$this->directory."\"");
		}
	}
	
	/**
	 * A project is valid if it has a root composer.json
	 */
	public function isValid() {
		return $this->directory && realpath($this->directory . '/composer.json');
	}
	
	/**
	 * Figure out the branch this composer is installed against
	 */
	public function getBranch() {
		$repository = new Repository($this->directory);
		$head = $repository->getHead();
		if($head instanceof Branch) {
			return $head->getName();
		}
	}
}
