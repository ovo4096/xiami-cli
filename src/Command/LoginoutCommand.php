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

        if ($this->getUserCache() === null) {
            $io->error('You are not logged in');
            return;
        }
        
        $this->deleteUserCache();
        $io->success('Logout successful');
    }
}
