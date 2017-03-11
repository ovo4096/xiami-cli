<?php
namespace Xiami\Console\Model;

use Xiami\Console\Exception\GetPlaylistJsonException;
use GuzzleHttp\Client;

class Song
{
    public $id;
    public $name;
    public $albumId;
    public $albumName;
    public $lyricistNames;
    public $artistNames;
    public $composerNames;
    public $arrangerNames;
    public $audioLinks = [];

    public static function getFromPlaylistJsonById($id)
    {
        $client = new Client();
        $response = $client->get("http://www.xiami.com/song/playlist/id/$id/object_name/default/object_id/0/cat/json");

        $json = json_decode((string) $response->getBody());

        if (!empty($json->message) || !$json->status) {
            throw new GetPlaylistJsonException($json->message);
        }

        return self::getFromPlaylistJson($json->data->trackList[0]);
    }

    public static function getFromPlaylistJson($json)
    {
        $song = new Song();
        $song->id = $json->songId + 0;
        $song->name = $json->songName;
        $song->albumId = $json->albumId + 0;
        $song->albumName = $json->album_name;
        $song->artistNames = $json->artist;
        $song->lyricerNames = $json->songwriters;
        $song->composerNames = $json->composer;
        $song->arrangerNames = $json->arrangement;

        usort($json->allAudios, function ($a, $b) {
            return $a->fileSize < $b->fileSize;
        });

        array_map(function ($audio) use ($song) {
            if (!isset($song->audioLinks[$audio->audioQualityEnum])) {
                $song->audioLinks[$audio->audioQualityEnum] = [];
            }
            $song->audioLinks[$audio->audioQualityEnum][] = $audio->filePath;
        }, $json->allAudios);

        return $song;
    }
}
