<?php

namespace SilverStripe\Cow\Model;

/**
 * A changelog which can be generated from a project
 */
class ChangeLog {
	
	/**
	 * @var Project
	 */
	protected $project;

	/**
	 * @var ReleaseVersion
	 */
	protected $fromVersion;
	
	public function __construct(Project $project, ReleaseVersion $fromVersion) {
		$this->project = $project;
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
		/*
		
		$format = "--pretty=tformat:\"message:%s|||author:%aN|||abbrevhash:%h|||hash:%H|||date:%ad|||timestamp:%at\"";
		$log = $this->exec("git log $format --date=short {$range}", true);
		
		
		$this->log(sprintf('Changing to directory "%s"', $path), Project::MSG_INFO);

		chdir("$this->baseDir/$path");  //switch to the module's path

		// Internal serialization format, ideally this would be JSON but we can't escape characters in git logs.
		$log = $this->exec("git log --pretty=tformat:\"message:%s|||author:%aN|||abbrevhash:%h|||hash:%H|||date:%ad|||timestamp:%at\" --date=short {$range}", true);

		chdir($this->baseDir);  //switch the working directory back

		return $log;*/
	}

	/**
	 * Get all changes grouped by type
	 */
	protected function getGroupedChanges() {
		$modules = $this->project->getModules();

		$changes = array();
		foreach($modules as $module) {
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
}
