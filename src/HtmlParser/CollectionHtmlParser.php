<?php
namespace Xiami\Console\HtmlParser;

use Symfony\Component\DomCrawler\Crawler;
use Xiami\Console\Model\CollectionSong;

class CollectionHtmlParser extends HtmlParser
{
    public function getTitle()
    {
        preg_match(
            '/(?<=h2>)[\s\S]*?(?:(?=<))/',
            $this->html,
            $matches
        );
        return trim(html_entity_decode($matches[0], ENT_QUOTES));
    }

    public function getMaker()
    {
        $crawler = new Crawler($this->html);
        return trim($crawler->filter('[name_card]')->text());
    }

    public function getTags()
    {
        preg_match(
            '/(?<=标签：<\/span>)[\s\S]*?(?=<\/l)/',
            $this->html,
            $matches
        );

        if (count($matches) === 0) {
            return null;
        }

        preg_match_all(
            '/(?<=>).*?(?=<)/',
            $matches[0],
            $matches
        );
        $tags = [];
        foreach ($matches[0] as $tag) {
            $tags[] = trim($tag);
        }
        return $tags;
    }

    public function getUpdateDate()
    {
        preg_match(
            '/(?<=更新时间：<\/span>)[\s\S]*?(?=<\/l)/',
            $this->html,
            $matches
        );
        return trim($matches[0]);
    }

    public function getIntroduction()
    {
        preg_match(
            '/full">[\s\S]*?>(?<intro>[\s\S]*?)<\/div>/',
            $this->html,
            $matches
        );
        if (count($matches) === 0) {
            return null;
        }
        return self::formatHtmlTextareaToConsoleTextblock($matches['intro']);
    }

    public function getTrackList()
    {
        $trackList = [];
        $crawler = new Crawler($this->html);

        $html = $crawler->filter('.quote_song_list > ul')->html();
        preg_match_all(
            '/(?<status>checked|disabled)[\s\S]*?e="(?<id>\d*)"[\s\S]*?e">(?<name>[\s\S]*?)<\/span[\s\S]*?“(?<intro>[\s\S]*?)”/',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as &$matche) {
            $matche['status'] = trim($matche['status']);
            $matche['name'] = trim(
                preg_replace(
                    '/\<.*?(\s*)?\/?\>/i',
                    '',
                    $matche['name']
                )
            );

            preg_match_all(
                '/\s*(?<name>.*)\s*--\s*(?<artist>.*)/',
                $matche['name'],
                $matches2,
                PREG_SET_ORDER
            );

            $song = new CollectionSong();
            $song->hasCopyright = $matche['status'] === 'checked' ? true : false;
            $song->id = $matche['id'] + 0;
            $song->title = trim($matches2[0]['name']);
            $song->artist = trim($matches2[0]['artist']);
            $song->introduction = self::formatHtmlTextareaToConsoleTextblock($matche['intro']);

            $trackList[] = $song;
        }

        return $trackList;
    }
}
