<?php

namespace SilverStripe\Cow;

use SilverStripe\Cow\Commands;
use Symfony\Component\Console;

class Application extends Console\Application
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

		// What is this cow doing in here, stop it, get out
        $commands[] = new Commands\MooCommand();

		// Release sub-commands
		$commands[] = new Commands\Release\Create();
		$commands[] = new Commands\Release\Branch();
		$commands[] = new Commands\Release\Translate();
		$commands[] = new Commands\Release\Changelog();
		$commands[] = new Commands\Release\Tag();
		$commands[] = new Commands\Release\Push();

		// Base release command
		$commands[] = new Commands\Release\Release();

		// Module commands
		$commands[] = new Commands\Module\Translate();

        return $commands;
    }
}
