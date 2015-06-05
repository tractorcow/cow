<?php

namespace SilverStripe\Cow\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MooCommand extends Command {

    protected function configure() {
        $this->setName("cow:moo")
             ->setDescription("Discuss with cow");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('moo');
    }
}