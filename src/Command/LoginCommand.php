<?php

namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class LoginCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('login')
            ->setDefinition([
                new InputArgument('username', InputArgument::REQUIRED, 'Your username'),
                new InputArgument('password', InputArgument::REQUIRED, 'Your password')
            ])
            ->setDescription('login description')
            ->setHelp('login help');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jar = new CookieJar();
        $client = new Client([
            'cookies' => $jar
        ]);

        $client->get('https://login.xiami.com/member/login');
        $xiamiToken = $jar->toArray()[1];

        $response = $client->post('https://login.xiami.com/passport/login', [
            'form_params' => [
                '_xiamitoken' => $xiamiToken['Value'],
                'account' => $input->getArgument('username'),
                'pw' => $input->getArgument('password'),
            ],
            'headers' => [
                'Referer' => 'https://login.xiami.com/member/login'
            ],
        ]);

        $result = json_decode((string) $response->getBody());
        if (!$result->status) {
            switch ($result->msg) {
                case '账号或密码错误':
                    $output->writeln('<error>Incorrect username or password!</error>');
                    break;
                case '请输入验证码':
                    $output->write('<error>Has exceeded the maximum number of retries!</error> ');
                    $output->writeln('(<info>Tip: change another IP and try again</info>)');
                    break;
                default:
                    $output->writeln('<error>unknown error!</error>');
                    break;
            }
        } else {
            $output->writeln('<info>login successful!</info>');
        }
    }
}
