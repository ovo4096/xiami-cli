<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Xiami\Console\Model\User;

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

        $cookies = $jar->toArray();
        $xiamiToken = $cookies[
            array_search(
                '_xiamitoken',
                array_column($cookies, 'Name')
            )
        ];

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
                    $output->writeln('<error>Incorrect username or password</error>');
                    break;
                case '请输入验证码':
                    $output->writeln('<error>Has exceeded the maximum number of retries</error>');
                    $output->writeln('<info>Tip: change another IP and try again</info>');
                    break;
                default:
                    $output->writeln('<error>unknown error</error>');
                    break;
            }
            return;
        }
        $output->writeln('<info>login successful</info>');

        $cookies = $jar->toArray();
        $authToken = $cookies[
            array_search(
                'member_auth',
                array_column($cookies, 'Name')
            )
        ]['Value'];
        $userInfoArray = explode(
            '"',
            urldecode(
                $cookies[
                    array_search(
                        'user',
                        array_column($cookies, 'Name')
                    )
                ]['Value']
            )
        );

        $user = new User();
        $user->id = $userInfoArray[0] + 0;
        $user->name = $userInfoArray[1];
        $matches = [];
        preg_match(
            '/(?<=>).*(?=<)/',
            $userInfoArray[5],
            $matches
        );
        $user->level = $matches[0];
        $user->playCount = $userInfoArray[8] + 0;
        $user->loginTimestamp = $userInfoArray[10] + 0;
        $user->authToken = $authToken;
        $user->followersCount = $userInfoArray[7] + 0;
        $user->followingCount = $userInfoArray[6] + 0;

        $cache = new FilesystemAdapter('xiami-cli');
        $userCache = $cache->getItem('user');
        $userCache->set($user);
        $cache->save($userCache);
    }
}
