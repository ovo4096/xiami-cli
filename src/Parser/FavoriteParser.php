<?php

namespace Xiami\Console\Parser;

use Xiami\Console\Model\Song;
use Xiami\Console\Model\Artist;

class FavoriteParser
{
    public static function getSongs($html)
    {
        $songMatches = [];
        preg_match_all(
            '/<tr\sdata-needpay="[\s\S]*?value="(?<id>\d*)"\s(?<inStock>checked|disabled)[\s\S]*?title="(?<name>.*?)"[\s\S]*?(?:(?<artists>(?:<a\sclass="artist_name"[\s\S]*?\/a>\s?)+)(?<=>)[\s\S]*?(?=<\/td>))[\s\S]*?value="(?<rate>\d)"[\s\S]*?\/tr>/',
            $html,
            $songMatches,
            PREG_SET_ORDER
        );

        $songs = [];
        foreach ($songMatches as $matche) {
            $song = new Song();
            $song->id = $matche['id'] + 0;
            $song->name = html_entity_decode($matche['name'], ENT_QUOTES);
            $song->inStock = $matche['inStock'] === 'checked';
            $song->rate = $matche['rate'] + 0;

            $artistMatches = [];
            preg_match_all(
                '/e="(?<name>.*?)"/',
                $matche['artists'],
                $artistMatches,
                PREG_SET_ORDER
            );

            foreach ($artistMatches as $matche) {
                $artist = new Artist();
                $artist->name = html_entity_decode($matche['name'], ENT_QUOTES);
                $song->artists[] = $artist;
            }

            $songs[] = $song;
        }

        return $songs;
    }
}
