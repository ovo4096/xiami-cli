<?php
namespace Xiami\Console\HtmlParser;

use Xiami\Console\Model\MySong;

class MySongsHtmlParser extends HtmlParser
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
        foreach ($matches as $match) {
            $song = new MySong();
            $song->id = $match['id'] + 0;
            $song->title = trim(html_entity_decode($match['name'], ENT_QUOTES));
            $song->hasCopyright = $match['inStock'] === 'checked';
            $song->rate = $match['rate'] + 0;
            $song->artist = $this->getArtists($match['artists']);
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

        if (!isset($matches[0][0])) return 1;

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

        if (!isset($matches[0][0])) return 1;

        return $matches[0][0] + 0;
    }

    private function getArtists($html)
    {
        $matches = [];
        preg_match_all(
            '/e="(?<name>.*?)"/',
            $html,
            $matches,
            PREG_SET_ORDER
        );
        $names = [];
        foreach ($matches as $match) {
            $names[] = trim(html_entity_decode($match['name'], ENT_QUOTES));
        }
        return implode(', ', $names);
    }
}
