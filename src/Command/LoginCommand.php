<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Xiami\Console\Style\AwesomeStyle;
use Xiami\Console\Model\User;
use Xiami\Console\Exception\UserLoginException;

class LoginCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('login')
            ->setDefinition([
                new InputArgument(
                    'username',
                    InputArgument::REQUIRED,
                    'Your username'
                ),
                new InputArgument(
                    'password',
                    InputArgument::REQUIRED,
                    'Your password'
                )
            ])
            ->setDescription('User login');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new AwesomeStyle($input, $output);
        
        $user = $this->getUserCache();
        if ($user !== null) {
            $io->error('You are already logged in as ' . $user->name);
            return;
        }

        try {
            $user = User::get(
                $input->getArgument('username'),
                $input->getArgument('password')
            );
            $io->success('Login successful');
            $this->setUserCache($user);
        } catch (UserLoginException $e) {
            $io->error($e->getMessage());
        }
    }
}
