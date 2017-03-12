<?php
namespace Xiami\Console\Parser;

use Xiami\Console\Model\Song;
use Xiami\Console\Model\Artist;

class FavoriteSongParser extends Parser
{
    public function getSongs()
    {
        $matches = [];
        preg_match_all(
            '/<tr\sdata-needpay="[\s\S]*?value="(?<id>\d*)"\s(?<inStock>checked|disabled)[\s\S]*?title="(?<name>.*?)"[\s\S]*?(?:(?<artists>(?:<a\sclass="artist_name"[\s\S]*?\/a>\s?)+)(?<=>)[\s\S]*?(?=<\/td>))[\s\S]*?value="(?<rate>\d)"[\s\S]*?\/tr>/',
            $this->html,
            $matches,
            PREG_SET_ORDER
        );

        $songs = [];
        foreach ($matches as $matche) {
            $song = new Song();
            $song->id = $matche['id'] + 0;
            $song->name = html_entity_decode($matche['name'], ENT_QUOTES);
            $song->inStock = $matche['inStock'] === 'checked';
            $song->rate = $matche['rate'] + 0;
            $song->artists = $this->getArtists($matche['artists']);

            $songs[] = $song;
        }

        return $songs;
    }

    public function getTotalPages()
    {
        $matches = [];
        preg_match_all(
            '/(?<=共)\d+(?=条\)<\/span>)/',
            $this->html,
            $matches
        );

        return ceil($matches[0][0] / 25);
    }

    public function getNumberOfCurrentPage()
    {
        $matches = [];
        preg_match_all(
            '/(?<=<span>\(第)\d+(?=页)/',
            $this->html,
            $matches
        );

        return $matches[0][0] + 0;
    }

    protected function getArtists($html)
    {
        $matches = [];
        preg_match_all(
            '/e="(?<name>.*?)"/',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        $artists = [];
        foreach ($matches as $matche) {
            $artist = new Artist();
            $artist->name = html_entity_decode($matche['name'], ENT_QUOTES);
            $artists[] = $artist;
        }

        return $artists;
    }
}
