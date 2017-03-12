<?php
namespace Xiami\Console\Model;

use GuzzleHttp\Client;
use Xiami\Console\Exception\GetPlaylistJsonException;
use Xiami\Console\HtmlParser\AlbumHtmlParser;

class Album
{
    public $id;
    public $tags = [];
    public $tackList = [];

    public static function get($id)
    {
        $client = new Client();
        $response = $client->get("http://www.xiami.com/song/playlist/id/$id/type/1/object_name/default/object_id/0/cat/json");

        $json = json_decode((string) $response->getBody());

        if ((!empty($json->message) && count($json->data->trackList) === 0) || !$json->status) {
            throw new GetPlaylistJsonException($json->message);
        }

        $album = new Album();
        
        $album->id = $id;

        array_map(function ($songJSON) use ($album) {
            $album->tackList[] = Song::fromPlaylistJson($songJSON);
        }, $json->data->trackList);

        $response = $client->get("http://www.xiami.com/album/$id");
        $html = (string) $response->getBody();

        $htmlParser = new AlbumHtmlParser($html);
        $album->tags = $htmlParser->getTags();

        return $album;
    }
}
