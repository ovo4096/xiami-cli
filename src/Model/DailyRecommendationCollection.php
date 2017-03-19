<?php
namespace Xiami\Console\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Xiami\Console\Exception\GetPlaylistJsonException;

class DailyRecommendationCollection
{
    public $trackList = [];

    public static function get($user = null)
    {
        $jar = new CookieJar();
        if ($user != null) {
            $jar->setCookie(new SetCookie([
                'Name' => 'member_auth',
                'Value' => $user->authToken,
                'Domain' => 'www.xiami.com',
                'Path' => '/',
                'Max-Age' => null,
                'Expires' => null,
                'Secure' => false,
                'Discard' => false,
                'HttpOnly' => false
            ]));
        }
        $json = json_decode((string)(new Client())->get(
            'http://www.xiami.com/song/playlist/id/1/type/9/object_name/default/object_id/0/cat/json',
            [
                'cookies' => $jar
            ]
        )->getBody());

        if (!$json->status) {
            throw new GetPlaylistJsonException($json->message);
        }

        $collection = new self();

        switch ($json->message) {
            case '应版权方要求，已过滤部分歌曲':
            case '抱歉，应版权方要求，没有歌曲可以播放~':
            case '':
                foreach ($json->data->trackList as $songJSON) {
                    $collection->trackList[] = Song::fromPlaylistJson($songJSON);
                }
                break;

            default:
                throw new GetPlaylistJsonException($json->message);
                break;
        }

        return $collection;
    }
}