<?php
namespace Xiami\Console\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Xiami\Console\Exception\UserLoginException;

class User
{
    public $id;
    public $name;
    public $level;
    public $loginTimestamp;
    public $followersCount;
    public $followingCount;
    public $playCount;
    public $authToken;

    public static function get($username, $password)
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
                'account' => $username,
                'pw' => $password,
            ],
            'headers' => [
                'Referer' => 'https://login.xiami.com/member/login'
            ],
        ]);

        $result = json_decode((string) $response->getBody());
        if (!$result->status) {
            throw new UserLoginException($result->msg);
        }

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

        return $user;
    }
}
