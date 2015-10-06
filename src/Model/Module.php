<?php

namespace SilverStripe\Cow\Model;

use Gitonomy\Git\Reference\Branch;
use Gitonomy\Git\Repository;
use InvalidArgumentException;

/**
 * A module installed in a project
 */
class Module {
	
	/**
	 * Parent project (installer module)
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
	
	public function __construct($directory, $name, Project $parent = null) {
		$this->directory = realpath($directory);
		$this->name = $name;
		$this->parent = $parent;
		
		if(!$this->isValid()) {
			throw new InvalidArgumentException("No module in directory \"{$this->directory}\"");
		}
	}

	/**
	 * Get the base directory this module is saved in
	 *
	 * @return string
	 */
	public function getDirectory() {
		return $this->directory;
	}

	/**
	 * Gets the module lang dir
	 *
	 * @return string
	 */
	public function getLangDirectory() {
		return $this->getMainDirectory() . '/lang';
	}

	/**
	 * Gets the directory(s) of the JS lang folder.
	 *
	 * Can be a string or an array result
	 *
	 * @return string|array
	 */
	public function getJSLangDirectory() {
		$dir = $this->getMainDirectory() . '/javascript/lang';

		// Special case for framework which has a nested 'admin' submodule
		if($this->getName() === 'framework') {
			return array(
				$this->getMainDirectory() . '/admin/javascript/lang',
				$dir
			);
		} else {
			return $dir;
		}

	}

	/**
	 * Directory where module files exist; Usually the one that sits just below the top level project
	 *
	 * @return string
	 */
	public function getMainDirectory() {
		return $this->getDirectory();
	}
	
	/**
	 * A project is valid if it has a root composer.json
	 */
	public function isValid() {
		return $this->directory && realpath($this->directory . '/composer.json');
	}

	/**
	 * Determine if this project has a .tx configured
	 *
	 * @return bool
	 */
	public function isTranslatable() {
		return $this->directory && realpath($this->directory . '/.tx/config');
	}

	/**
	 * Get name of this module
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Get github team name (normally 'silverstripe')
	 * 
	 * @return string
	 */
	public function getTeam() {
		switch($this->name) {
			case 'reports':
				return 'silverstripe-labs';
			default:
				return 'silverstripe';
		}
	}
	
	/**
	 * Get link to github module
	 * 
	 * @return string
	 */
	public function getLink() {
		$team = $this->getTeam();
		$name = $this->getName();
		return "https://github.com/{$team}/silverstripe-{$name}/";
	}
	
	/**
	 * Get git repo for this module
	 * 
	 * @return Repository
	 */
	public function getRepository() {
		return new Repository($this->directory, array(
			'environment_variables' => array('HOME' => getenv('HOME'))
		));
	}
	
	/**
	 * Figure out the branch this composer is installed against
	 */
	public function getBranch() {
		$head = $this
			->getRepository()
			->getHead();
		if($head instanceof Branch) {
			return $head->getName();
		}
	}

	/**
	 * Change to another branch, creating it if it doesn't exist
	 *
	 * @param string $branch
	 */
	public function changeBranch($branch) {
		$repo = $this->getRepository();
		$repo->run('checkout', array('-B', $branch));
	}

	/**
	 * Tag this module
	 *
	 * @param string $tag
	 */
	public function addTag($tag) {
		$repo = $this->getRepository();
		$repo->run('tag', array('-a', $tag, '-m', "Release {$tag}"));
	}

	/**
	 * Push this branch to the given remote
	 *
	 * @param string $remote
	 * @param bool $tags Push tags?
	 */
	public function pushTo($remote = 'origin', $tags = false) {
		// Validate branch
		$branch = $this->getBranch();
		if(!$branch) {
			throw new Exception("Module " . $this->getName() . " cannot push without a current branch");
		}

		// Check options
		$repo = $this->getRepository();
		$args = array($remote, "refs/heads/{$branch}");
		if($tags) {
			$args[] = '--follow-tags';
		}

		// Push
		$repo->run('push', $args);
	}
}
