<?php
namespace Xiami\Console\Model;

use Xiami\Console\Exception\GetPlaylistJsonException;
use GuzzleHttp\Client;

class Song
{
    public $id;
    public $albumId;
    public $tags = [];

    public $lyricsUrl;
    public $audioUrls = [];

    public $hasCopyright = false;

    public static function get($id)
    {
        $client = new Client();
        $response = $client->get("http://www.xiami.com/song/playlist/id/$id/object_name/default/object_id/0/cat/json");

        $json = json_decode((string) $response->getBody());

        if ((!empty($json->message) && count($json->data->trackList) === 0) || !$json->status) {
            throw new GetPlaylistJsonException($json->message);
        }

        return self::fromPlaylistJson($json->data->trackList[0]);
    }

    public static function fromPlaylistJson($json)
    {
        $song = new Song();
        $song->id = $json->songId + 0;
        $song->albumId = $json->albumId + 0;
        $song->lyricsUrl = $json->lyric_url;
        $song->hasCopyright = true;

        $song->tags['Title'] = trim(html_entity_decode($json->songName, ENT_QUOTES));
        $song->tags['Album'] = trim(html_entity_decode($json->album_name, ENT_QUOTES));
        $song->tags['Artist'] = trim(html_entity_decode($json->artist, ENT_QUOTES));
        $song->tags['Lyricist'] = trim(html_entity_decode($json->songwriters, ENT_QUOTES));
        $song->tags['Composer'] = trim(html_entity_decode($json->composer, ENT_QUOTES));
        $song->tags['Arranger'] = trim(html_entity_decode($json->arrangement, ENT_QUOTES));

        usort($json->allAudios, function ($a, $b) {
            return $a->fileSize < $b->fileSize;
        });

        array_map(function ($audioJSON) use ($song) {
            if (!isset($song->audioUrls[$audioJSON->audioQualityEnum])) {
                $song->audioUrls[$audioJSON->audioQualityEnum] = [];
            }
            $song->audioUrls[$audioJSON->audioQualityEnum][] = trim($audioJSON->filePath);
        }, $json->allAudios);

        return $song;
    }
}
