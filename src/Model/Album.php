<?php
namespace Xiami\Console\Model;

use GuzzleHttp\Client;
use Xiami\Console\Exception\GetPlaylistJsonException;
use Xiami\Console\HtmlParser\AlbumHtmlParser;

class Album
{
    public $id;
    public $tags = [];
    public $trackList = [];

    public static function get($id)
    {
        $client = new Client();
        $response = $client->get("http://www.xiami.com/song/playlist/id/$id/type/1/object_name/default/object_id/0/cat/json");

        $json = json_decode((string) $response->getBody());

        if (!$json->status) {
            throw new GetPlaylistJsonException($json->message);
        }

        $album = new Album();
        $album->id = $id;
        
        if (!empty($json->message) && count($json->data->trackList) !== 0) {
            foreach ($json->data->trackList as $songJSON) {
                $album->trackList[] = Song::fromPlaylistJson($songJSON);
            }
        }

        $response = $client->get("http://www.xiami.com/album/$id");
        $html = (string) $response->getBody();
        $htmlParser = new AlbumHtmlParser($html);
        $album->tags = $htmlParser->getTags();

        if ($json->message === '应版权方要求，已过滤部分歌曲' || $json->message === '抱歉，应版权方要求，没有歌曲可以播放~') {
            $fullTrackList = $htmlParser->getTrackList();
            foreach ($fullTrackList as &$song) {
                if ($song->hasCopyright) {
                    $result = array_filter($album->trackList, function ($newSong) use ($song) {
                        return $newSong->id === $song->id;
                    });
                    $song = array_shift($result);
                }
            }
            $album->trackList = $fullTrackList;
        }

        return $album;
    }
}
