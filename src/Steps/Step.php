<?php

namespace SilverStripe\Cow\Steps;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Step {
	abstract public function run(InputInterface $input, OutputInterface $output);
}
