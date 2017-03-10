<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class LoginoutCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('loginout')
            ->setDescription('loginout description')
            ->setHelp('loginout help');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cache = new FilesystemAdapter('xiami-cli');
        $userCache = $cache->getItem('user');

        if (!$userCache->isHit()) {
            $output->writeln('<error>You are not logged in</error>');
            return;
        }

        $cache->deleteItem('user');
        $output->writeln('<info>Logout successful</info>');
    }
}
