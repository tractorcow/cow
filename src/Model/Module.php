<?php

namespace SilverStripe\Cow\Model;

/**
 * A module installed in a project
 */
class Module {
	
	/**
	 *
	 * @var Project
	 */
	protected $parent;
	
	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * Directory of this module
	 * Doesn't always match name (e.g. installer)
	 *
	 * @var string
	 */
	protected $directory;
	
	public function __construct(Project $parent, $name, $directory) {
		$this->parent = $parent;
		$this->name = $name;
		$this->directory = $directory;
	}
	
	public function getName() {
		return $this->name;
	}
}
