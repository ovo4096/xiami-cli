<?php
namespace Xiami\Console\Model;

use GuzzleHttp\Client;
use Xiami\Console\Exception\GetPlaylistJsonException;
use Xiami\Console\HtmlParser\CollectionHtmlParser;

class Collection
{
    public $id;

    public $title;
    public $maker;
    public $updateDate;
    public $introduction;
    public $tags = [];

    public $trackList = [];

    public static function get($id)
    {
        $client = new Client();
        $response = $client->get("http://www.xiami.com/song/playlist/id/$id/type/3/object_name/default/object_id/0/cat/json");

        $json = json_decode((string) $response->getBody());

        if (!$json->status) {
            throw new GetPlaylistJsonException($json->message);
        }

        $collection = new Collection();

        switch ($json->message) {
            case '应版权方要求，已过滤部分歌曲':
            case '抱歉，应版权方要求，没有歌曲可以播放~':
            case '':
                $collection->id = $id + 0;

                $response = $client->get("http://www.xiami.com/collect/$id");
                $html = (string) $response->getBody();
                $htmlParser = new CollectionHtmlParser($html);

                $collection->title = $htmlParser->getTitle();
                $collection->maker = $htmlParser->getMaker();
                $collection->tags = $htmlParser->getTags();
                $collection->updateDate = $htmlParser->getUpdateDate();
                $collection->introduction = $htmlParser->getIntroduction();
                $fullTrackList = $htmlParser->getTrackList();

                foreach ($json->data->trackList as $songJSON) {
                    $collection->trackList[] = CollectionSong::fromPlaylistJson($songJSON);
                }

                foreach ($fullTrackList as &$song) {
                    if ($song->hasCopyright) {
                        $result = array_filter($collection->trackList, function ($newSong) use ($song) {
                            return $newSong->id === $song->id;
                        });
                        $replaceSong = array_shift($result);
                        $replaceSong->introduction = $song->introduction;
                        $song = $replaceSong;
                    }
                }

                $collection->trackList = $fullTrackList;
                break;
                
            default:
                throw new GetPlaylistJsonException($json->message);
                break;
        }

        return $collection;
    }
}
