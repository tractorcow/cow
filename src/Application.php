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
		$commands[] = new Commands\Project\Create();
		$commands[] = new Commands\Release\Changelog();

        return $commands;
    }
}
