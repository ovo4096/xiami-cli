<?php
namespace Xiami\Console\Model;

use Xiami\Console\Exception\GetPlaylistJsonException;
use GuzzleHttp\Client;

class Song
{
    public const LOSSLESS_QUALITY = 'LOSSLESS';
    public const HIGH_QUALITY = 'HIGH';
    public const LOW_QUALITY = 'LOW';

    public $id;
    public $title;
    public $artist;
    public $lyricist;
    public $composer;
    public $arranger;
    
    public $albumId;
    public $albumTitle;

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
        $song->title = trim(html_entity_decode($json->songName, ENT_QUOTES));
        $song->artist = trim(html_entity_decode($json->artist, ENT_QUOTES));
        $song->lyricist = trim(html_entity_decode($json->songwriters, ENT_QUOTES));
        $song->composer = trim(html_entity_decode($json->composer, ENT_QUOTES));
        $song->arranger = trim(html_entity_decode($json->arrangement, ENT_QUOTES));
        
        $song->albumId = $json->albumId + 0;
        $song->albumTitle = trim(html_entity_decode($json->album_name, ENT_QUOTES));

        $song->lyricsUrl = $json->lyric_url;
        $song->hasCopyright = true;

        usort($json->allAudios, function ($a, $b) {
            return $a->fileSize < $b->fileSize;
        });

        foreach ($json->allAudios as $audioJSON) {
            if (!isset($song->audioUrls[$audioJSON->audioQualityEnum])) {
                $song->audioUrls[$audioJSON->audioQualityEnum] = [];
            }
            $song->audioUrls[$audioJSON->audioQualityEnum][] = trim($audioJSON->filePath);
        }

        return $song;
    }
}
