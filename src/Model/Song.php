<?php
namespace Xiami\Console\Model;

use Xiami\Console\Exception\GetPlaylistJsonException;
use GuzzleHttp\Client;
use Xiami\Console\Helper\Helper;

class Song
{
    const LOSSLESS_QUALITY = 'LOSSLESS';
    const HIGH_QUALITY = 'HIGH';
    const LOW_QUALITY = 'LOW';

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

        $json = json_decode((string)$response->getBody());

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

        $client = new Client();
        $response = $client->get(
            'http://www.xiami.com/song/gethqsong/sid/' . $song->id,
            [
                'headers' => [
                    'Referer' => 'http://www.xiami.com/'
                ]
            ]
        );
        $hqJSON = json_decode((string)$response->getBody());
//        usort($json->allAudios, function ($a, $b) {
//            return $a->fileSize < $b->fileSize;
//        });
//
//        foreach ($json->allAudios as $audioJSON) {
//            if (!isset($song->audioUrls[$audioJSON->audioQualityEnum])) {
//                $song->audioUrls[$audioJSON->audioQualityEnum] = [];
//            }
//            $song->audioUrls[$audioJSON->audioQualityEnum][] = trim($audioJSON->filePath);
//        }
        $song->audioUrls[Song::LOSSLESS_QUALITY] = [];
        $song->audioUrls[Song::HIGH_QUALITY] = [Helper::getLocation($hqJSON->location)];
        $song->audioUrls[Song::LOW_QUALITY] = [Helper::getLocation($json->location)];

        return $song;
    }

    public function merge($song)
    {
        if (!isset($this->id) && isset($song->id)) {
            $this->id = $song->id;
        }

        if (!isset($this->albumId) && isset($song->albumId)) {
            $this->albumId = $song->albumId;
        }

        if (empty($this->title) && !empty($song->title)) {
            $this->title = $song->title;
        }

        if (empty($this->artist) && !empty($song->artist)) {
            $this->artist = $song->artist;
        }

        if (empty($this->lyricist) && !empty($song->lyricist)) {
            $this->lyricist = $song->lyricist;
        }

        if (empty($this->composer) && !empty($song->composer)) {
            $this->composer = $song->composer;
        }

        if (empty($this->arranger) && !empty($song->arranger)) {
            $this->arranger = $song->arranger;
        }

        if (empty($this->albumTitle) && !empty($song->albumTitle)) {
            $this->albumTitle = $song->albumTitle;
        }

        if (empty($this->lyricsUrl) && !empty($song->lyricsUrl)) {
            $this->lyricsUrl = $song->lyricsUrl;
        }

        if (count($this->audioUrls) === 0 && count($song->audioUrls) > 0) {
            $this->audioUrls = $song->audioUrls;
        }

        $this->hasCopyright = $this->hasCopyright || $song->hasCopyright;
    }
}
