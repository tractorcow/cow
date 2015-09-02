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

        $commands[] = new Commands\MooCommand();
		$commands[] = new Commands\Release\Create();
		$commands[] = new Commands\Release\Branch();
		$commands[] = new Commands\Release\Changelog();
		$commands[] = new Commands\Release\Release();
		$commands[] = new Commands\Release\Translate();
		
		$commands[] = new Commands\Module\Translate();

        return $commands;
    }
}
