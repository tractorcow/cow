<?php

namespace SilverStripe\Cow\Model;

/**
 * A changelog which can be generated from a project
 */
class ChangeLog {

	/**
	 * List of source modules
	 *
	 * @var Module[]
	 */
	protected $modules;

	/**
	 * @var ReleaseVersion
	 */
	protected $fromVersion;

	/**
	 * Create a new changelog
	 *
	 * @param array $modules Source of modules to generate changelog from
	 * @param ReleaseVersion $fromVersion
	 */
	public function __construct(array $modules, ReleaseVersion $fromVersion) {
		$this->modules = $modules;
		$this->fromVersion = $fromVersion;
	}

	/**
	 * Get the list of changes for this module
	 * 
	 * 
	 * @param Module $module
	 * @return array
	 */
	protected function getModuleLog(Module $module) {
		// Get raw log
		$range = $this->fromVersion->getValue()."..HEAD";
		$log = $module->getRepository()->getLog($range);
		
		$items = array();
		foreach($log->getCommits() as $commit) {
			$change = new ChangelogItem($module, $commit);
			
			// Skip ignored items
			if(!$change->isIgnored()) {
				$items[] = $change;
			}
		}
		return $items;
	}

	/**
	 * Get all changes grouped by type
	 *
	 * @return array
	 */
	protected function getGroupedChanges() {
		$changes = array();
		foreach($this->getModules() as $module) {
			$moduleChanges = $this->getModuleLog($module);
			$changes = array_merge($changes, $moduleChanges);
		}

		// Sort by type
		return $this->sortByType($changes);
	}

	/**
	 * Generate output in markdown format
	 *
	 * @return string
	 */
	public function getMarkdown() {
		$groupedLog = $this->getGroupedChanges();

		// Convert to string and generate markdown (add list to beginning of each item)
		$output = "\n\n## Change Log\n";
		foreach($groupedLog as $groupName => $commits) {
			if(empty($commits)) {
				continue;
			}
			
			$output .= "\n### $groupName\n\n";
			foreach($commits as $commit) {
				$output .= $commit->getMarkdown();
			}
		}

		return $output;
	}

	/**
	 * Sort and filter this list of commits into a grouped array of commits
	 *
	 * @param array $commits Flat list of commits
	 * @return array Nested list of commit categories, each of which is a list of commits in that category.
	 * Empty categories are still returned
	 */
	protected function sortByType($commits) {
		// sort by timestamp newest to oldest
		usort($commits, function($a, $b) {
			$aTime = $a->getDate();
			$bTime = $b->getDate();
			if($bTime == $aTime) {
				return 0;
			} elseif($bTime < $aTime) {
				return -1;
			} else {
				return 1;
			}
		});

		// List types
		$groupedByType = array();
		foreach(ChangeLogItem::get_types() as $type) {
			$groupedByType[$type] = array();
		}

		// Group into type
		foreach($commits as $commit) {
			$type = $commit->getType();
			if($type) {
				$groupedByType[$type][] = $commit;
			}
		}

		return $groupedByType;
	}

	/**
	 * Get modules for this changelog
	 *
	 * @return Module[]
	 */
	protected function getModules() {
		return $this->modules;
	}
}
