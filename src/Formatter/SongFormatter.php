<?php
namespace Xiami\Console\Formatter;

use Xiami\Console\Model\Song;

class SongFormatter
{
    public $song;

    public function __construct(Song $song)
    {
        $this->song = $song;
    }

    public function artistsToString()
    {
        $artistNames = array_map(function ($artist) {
            return $artist->name;
        }, $this->song->artists);
        return implode(', ', $artistNames);
    }

    public function rateToString()
    {
        $rateString = '☆☆☆☆☆';
        switch ($this->song->rate) {
            case Song::RATE_LOWER:
                $rateString = '★☆☆☆☆';
                break;
            case Song::RATE_LOW:
                $rateString = '★★☆☆☆';
                break;
            case Song::RATE_MEDIUM:
                $rateString = '★★★☆☆';
                break;
            case Song::RATE_HIGH:
                $rateString = '★★★★☆';
                break;
            case Song::RATE_HIGHER:
                $rateString = '★★★★★';
                break;
            default:
                break;
        }
        return $rateString;
    }
}
