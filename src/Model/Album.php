<?php
namespace Xiami\Console\Model;

use GuzzleHttp\Client;
use Xiami\Console\Exception\GetPlaylistJsonException;
use Xiami\Console\HtmlParser\AlbumHtmlParser;

class Album
{
    public $id;

    public $title;
    public $artist;
    public $language;
    public $publisher;
    public $releaseDate;
    public $genre;
    public $summary;

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

        switch ($json->message) {
            case '应版权方要求，已过滤部分歌曲':
            case '抱歉，应版权方要求，没有歌曲可以播放~':
            case '':
                $album->id = $id + 0;

                $response = $client->get("http://www.xiami.com/album/$id");
                $html = (string) $response->getBody();
                $htmlParser = new AlbumHtmlParser($html);

                $album->title = $htmlParser->getTitle();
                $album->artist = $htmlParser->getArtist();
                $album->language = $htmlParser->getLanguage();
                $album->publisher = $htmlParser->getPublisher();
                $album->releaseDate = $htmlParser->getReleaseDate();
                $album->genre = $htmlParser->getGenre();
                $album->summary = $htmlParser->getSummary();
                $fullTrackList = $htmlParser->getTrackList();

                foreach ($json->data->trackList as $songJSON) {
                    $album->trackList[] = Song::fromPlaylistJson($songJSON);
                }

                foreach ($fullTrackList as &$song) {
                    if ($song->hasCopyright) {
                        $result = array_filter($album->trackList, function ($newSong) use ($song) {
                            return $newSong->id === $song->id;
                        });
                        $replaceSong = array_shift($result);
                        $replaceSong->merge($song);

                        if (empty($replaceSong->artist) && !empty($album->artist)) {
                            $replaceSong->artist = $album->artist;
                        }

                        $song = $replaceSong;
                    }
                }

                $album->trackList = $fullTrackList;
                break;

            default:
                throw new GetPlaylistJsonException($json->message);
                break;
        }

        return $album;
    }
}
