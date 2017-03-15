<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Xiami\Console\Style\AwesomeStyle;

class LoginoutCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('loginout')
            ->setDescription('User loginout');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new AwesomeStyle($input, $output);
        $userCache = $this->cache->getItem('user');

        if (!$userCache->isHit()) {
            $io->error('You are not logged in');
            return;
        }

        $this->cache->deleteItem('user');
        $io->success('Logout successful');
    }
}
