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
    public $lyricLink;
    public $audioLinks = [];

    public static function getById($id)
    {
        $client = new Client();
        $response = $client->get("http://www.xiami.com/song/playlist/id/$id/object_name/default/object_id/0/cat/json");

        $json = json_decode((string) $response->getBody());

        if ((!empty($json->message) && count($json->data->trackList) === 0) || !$json->status) {
            throw new GetPlaylistJsonException($json->message);
        }

        return Song::fromPlaylistJson($json->data->trackList[0]);
    }

    public static function fromPlaylistJson($json)
    {
        $song = new Song();
        $song->id = $json->songId + 0;
        $song->name = html_entity_decode($json->songName, ENT_QUOTES);
        $song->albumId = $json->albumId + 0;
        $song->albumName = html_entity_decode($json->album_name, ENT_QUOTES);
        $song->artistNames = html_entity_decode($json->artist, ENT_QUOTES);
        $song->lyricerNames = html_entity_decode($json->songwriters, ENT_QUOTES);
        $song->composerNames = html_entity_decode($json->composer, ENT_QUOTES);
        $song->arrangerNames = html_entity_decode($json->arrangement, ENT_QUOTES);
        $song->lyricLink = $json->lyric_url;

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
