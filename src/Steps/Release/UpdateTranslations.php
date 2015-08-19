<?php

namespace SilverStripe\Cow\Steps\Release;

use InvalidArgumentException;
use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\Module;
use SilverStripe\Cow\Model\Project;
use SilverStripe\Cow\Steps\Step;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Synchronise all translations with transifex, merging these with strings detected in code files
 *
 * Basic process follows:
 *  - Set mtime on all local files to long ago (1 year in the past?) because tx pull breaks on new files and won't update them
 *  - Pull all source files from transifex with the below:
 *      `tx pull -a -s -f --minimum-perc=10`
 *  - Detect all new translations, making sure to merge in changes
 *      `./framework/sake dev/tasks/i18nTextCollectorTask "flush=all" "merge=1"
 *  - Detect all new JS translations in a similar way (todo)
 *  - Generate javascript from js source files
 *      `phing -Dmodule=my-module translation-generate-javascript-for-module`
 *  - Push up all source translations
 *      `tx push -s`
 *  - Commit changes to source control (without push)
 */
class UpdateTranslations extends Step {

	/**
	 * Min tx client version
	 
	 * @var string
	 */
	protected $txVersion = '0.11';

	/**
	 * Min % difference required for tx updates
	 *
	 * @var int
	 */
	protected $txMinimumPerc = 10;

	/**
	 * @var Project
	 */
	protected $project;

	/**
	 *
	 * @var array
	 */
	protected $modules;

	/**
	 * Create a new translation step
	 *
	 * @param Command $command
	 * @param string $directory Where to translate
	 * @param array $modules Optional list of modules to limit translation to
	 */
	public function __construct(Command $command, $directory, $modules = array()) {
		parent::__construct($command);

		$this->modules = $modules;
		$this->project = new Project($directory);
	}

	/**
	 * @return Project
	 */
	public function getProject() {
		return $this->project;
	}

	/**
	 *
	 * @return array
	 */
	public function getModules() {
		return $this->modules;
	}

	public function getStepName() {
		return 'translations';
	}

	public function run(InputInterface $input, OutputInterface $output) {
		$modules = $this->getModuleItems($output);
		$this->log($output, sprintf("Updating translations for %d module(s)", count($modules)));
		$this->checkVersion($output);
		$this->pullSource($output, $modules);
		$this->collectStrings($output, $modules);
		$this->generateJavascript($output, $modules);
		$this->pushSource($output, $modules);
		$this->commitChanges($output, $modules);
		$this->log($output, 'Translations complete');
	}

	/**
	 * Test that tx tool is installed
	 */
	protected function checkVersion(OutputInterface $output) {
		$result = $this->runCommand($output, array("tx", "--version"));
		if(!version_compare($result, $this->txVersion, '<')) {
			throw new InvalidArgumentException(
				"translate requires transifex {$this->txVersion} at least. "
					."Run 'pip install transifex-client==0.11b3' to update. "
					."Current version: ".$result
			);
		}

		$this->log($output, "Using transifex CLI version: $result");
	}

	/**
	 * Update sources from transifex
	 * 
	 * @param OutputInterface $output
	 * @param Module[] $modules List of modules
	 */
	protected function pullSource(OutputInterface $output, $modules) {
		$this->log($output, "Pulling sources from transifex (min %{$this->txMinimumPerc} delta)");

		foreach($modules as $module) {
			// Set mtime to a year ago so that transifex will see these as obsolete
			$touchCommand = sprintf(
				'find %s -type f \( -name "*.yml" \) -exec touch -t %s {} \;',
				$module->getLangDirectory(),
				date('YmdHi.s', strtotime('-1 year'))
			);
			$this->runCommand($output, $touchCommand);

			// Run tx pull
			$pullCommand = sprintf(
				'(cd %s && tx pull -a -s -f --minimum-perc=%d)',
				$module->getDirectory(),
				$this->txMinimumPerc
			);
			$this->runCommand($output, $pullCommand);
		}
	}

	/**
	 * Run text collector on the given modules
	 *
	 * @param OutputInterface $output
	 * @param Module[] $modules List of modules
	 */
	protected function collectStrings(OutputInterface $output, $modules) {
		$this->log($output, "Running i18nTextCollectorTask");

		// Get code dirs for each module
		$dirs = array();
		foreach($modules as $module) {
			$dirs[] = basename($module->getMainDirectory());
		}

		$sakeCommand = sprintf(
			'(cd %s && ./framework/sake dev/tasks/i18nTextCollectorTask "flush=all" "merge=1" "module=%s")',
			$this->getProject()->getDirectory(),
			implode(',', $dirs)
		);
		$this->runCommand($output, $sakeCommand);
	}

	/**
	 * Generate javascript for all modules
	 *
	 * @param OutputInterface $output
	 * @param Module[] $modules
	 */
	protected function generateJavascript(OutputInterface $output, $modules) {
		$this->log($output, "Generating javascript locale files");
		// Check which paths in each module require processing
		$count = 0;
		foreach($modules as $module) {
			$base = $module->getMainDirectory();
			$jsPath = $module->getJSLangDirectory();
			foreach((array)$jsPath as $path) {
				$count += $this->generateJavascriptInDirectory($output, $base, $path);
			}
		}
		$this->log($output, "Finished generating {$count} files");
	}


	/**
	 * Process all javascript in a given path
	 * 
	 * @param OutputInterface $output
	 * @param string $base Base directory of the module
	 * @param string $path Path to the location of JS files
	 * @return int Number of files generated
	 */
	protected function generateJavascriptInDirectory(OutputInterface $output, $base, $path) {
		// Iterate through each source file
		$count = 0;
		$template = <<<TMPL
// This file was generated by silverstripe/cow from %FILE%.
// See https://github.com/tractorcow/cow for details
if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
	if(typeof(console) != 'undefined') console.error('Class ss.i18n not defined');
} else {
	ss.i18n.addDictionary('%LOCALE%', %TRANSLATIONS%);
}
TMPL;
		// Update each source file
		foreach(glob("{$path}/src/*.js") as $sourceFile) {
			$count++;
			// Get contents and location
			$sourceContents = file_get_contents($sourceFile);
			$locale = preg_replace('/\.js$/','', basename($sourceFile));
			$targetFile = dirname(dirname($sourceFile)) . '/' . $locale . '.js';

			if(OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
				$this->log($output, "Generating file {$targetFile}", "info");
			}

			file_put_contents(
				$targetFile,
				str_replace(
					array(
						'%TRANSLATIONS%',
						'%FILE%',
						'%LOCALE%'
					),
					array(
						$sourceContents,
						substr($sourceFile, strlen($base) + 1), // Trim off base dir
						$locale
					),
					$template
				)
			);
		}
		return $count;
	}
	
	/**
	 * Push source updates to transifex
	 *
	 * @param OutputInterface $output
	 * @param type $modules
	 */
	public function pushSource(OutputInterface $output, $modules) {
		$this->log($output, "Pushing updated sources to transifex");

		foreach($modules as $module) {
			// Run tx pull
			$pushCommand = sprintf(
				'(cd %s && tx push -s)',
				$module->getDirectory()
			);
			$this->runCommand($output, $pushCommand);
		}
	}
	
	/**
	 * Commit changes for all modules
	 *
	 * @param OutputInterface $output
	 * @param Module[] $modules
	 */
	public function commitChanges(OutputInterface $output, $modules) {
		$this->log($output, 'Committing translations to git');
		
		foreach($modules as $module) {
			$repo = $module->getRepository();

			// Add all changes
			$jsPath = $module->getJSLangDirectory();
			$langPath = $module->getLangDirectory();
			foreach(array_merge((array)$jsPath, (array)$langPath) as $path) {
				$repo->run("add", array($path . "/*"));
			}
			
			$repo->run("commit", array("-m", "Update translations"));
		}
	}

	/**
	 * Get the list of module objects to translate
	 *
	 * @param OutputInterface
	 * @return Module[]
	 */
	protected function getModuleItems(OutputInterface $output) {
		$modules = $this->getProject()->getModules();
		$filter = $this->getModules();

		// Get only modules with translations
		$self = $this;
		return array_filter($modules, function($module) use ($output, $filter, $self) {
			// Automatically skip un-translateable modules
			if(empty($filter)) {
				return $module->isTranslatable();
			}
			
			// Skip filtered
			if(!in_array($module->getName(), $filter)) {
				return false;
			}

			// Warn if this module has no translations
			if(!$module->isTranslatable()) {
				$self->log(
					$output,
					sprintf("Selected module %s has no .tx/config directory", $module->getName()),
					"error"
				);
				return false;
			}

			return true;
		});
	}
}
