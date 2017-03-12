<?php
namespace Xiami\Console\Model;

use GuzzleHttp\Client;
use Xiami\Console\Exception\GetPlaylistJsonException;

class Album
{
    public $songs = [];

    public static function get($id)
    {
        $client = new Client();
        $response = $client->get("http://www.xiami.com/song/playlist/id/$id/type/1/object_name/default/object_id/0/cat/json");

        $json = json_decode((string) $response->getBody());

        if ((!empty($json->message) && count($json->data->trackList) === 0) || !$json->status) {
            throw new GetPlaylistJsonException($json->message);
        }

        $album = new Album();

        array_map(function ($json) use ($album) {
            $album->songs[] = Song::fromPlaylistJson($json);
        }, $json->data->trackList);

        $response = $client->get("http://www.xiami.com/album/$id");

        return $album;
    }

    public static function getInfo()
    {
    }
}
