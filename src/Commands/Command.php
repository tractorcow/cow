<?php

namespace SilverStripe\Cow\Commands;

use Symfony\Component\Console;

abstract class Command extends Console\Command\Command
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var Console\Input\InputInterface
     */
    protected $input;

    /**
     * @var Console\Output\OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription($this->description);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->fire();
    }

    /**
     * Defers to the subclass functionality.
     */
    abstract protected function fire();
}
