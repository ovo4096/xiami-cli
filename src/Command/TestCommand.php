<?php

namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected function configure()
    {
        $this->setName('test')->setDescription('Output Hello!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>$output</info>');
    }
}
