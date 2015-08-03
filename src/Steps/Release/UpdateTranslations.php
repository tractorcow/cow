<?php

namespace SilverStripe\Cow\Steps\Release;

use SilverStripe\Cow\Steps\Step;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Synchronise all translations with transifex, merging these with strings detected in code files
 */
class UpdateTranslations extends Step {

	public function getStepName() {
		return 'translations';
	}

	public function run(InputInterface $input, OutputInterface $output) {
		// Basic process follows:
		// * Pull all source files from transifex with the below:
		//     `tx pull -a -s -f --minimum-perc=10`
		// * Detect all new translations, making sure to merge in changes
		//     `./framework/sake dev/tasks/i18nTextCollectorTask "flush=all" "merge=1"
		// * Detect all new JS translations in a similar way (todo)
		// * Generate javascript from js source files
		//     `phing -Dmodule=my-module translation-generate-javascript-for-module`
		// * Push up all source translations
		//     `tx push -s`
	}
}
