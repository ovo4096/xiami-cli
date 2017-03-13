<?php
namespace Xiami\Console\HtmlParser;

use Symfony\Component\DomCrawler\Crawler;
use Xiami\Console\Model\Song;

class AlbumHtmlParser extends HtmlParser
{
    public function setInfoTo($album)
    {
        $crawler = new Crawler($this->html);
        $crawlerTagDOMs = $crawler->filter('#album_info table tr');
        foreach ($crawlerTagDOMs as $tagDOM) {
            $crawlerTagDOM = new Crawler($tagDOM);
            switch ($crawlerTagDOM->children()->eq(0)->text()) {
                case '艺人：':
                    $album->artist = trim($crawlerTagDOM->children()->eq(1)->text());
                    break;
                case '语种：':
                    $album->language = trim($crawlerTagDOM->children()->eq(1)->text());
                    break;
                case '唱片公司：':
                    $album->publisher = trim($crawlerTagDOM->children()->eq(1)->text());
                    break;
                case '发行时间：':
                    $album->releaseDate = trim($crawlerTagDOM->children()->eq(1)->text());
                    break;
                case '专辑类别：':
                    $album->genre = trim($crawlerTagDOM->children()->eq(1)->text());
                    break;
                default:
                    break;
            }
        }

        $matches = [];
        preg_match(
            '/.*?(?:(?=<))|.*/',
            $crawler->filter('h1')->html(),
            $matches
        );
        $album->title = html_entity_decode($matches[0], ENT_QUOTES);
    }

    public function getTrackList()
    {
        $trackList = [];
        $crawler = new Crawler($this->html);
        
        $crawlerTrackListDOMs = $crawler->filter('#track_list tr[data-needpay]');
        foreach ($crawlerTrackListDOMs as $dom) {
            $crawlerTrackDOM = new Crawler($dom);
            $matches = [];
            preg_match(
                '/\s(?<status>checked|disabled).*?value="(?<id>\d*)"[\s\S]*?"">\s*(?<title>.*?)\s*<\/a>\s*(?<artist>.*?)\s*?</',
                $crawlerTrackDOM->html(),
                $matches
            );

            $song = new Song();
            $song->hasCopyright = $matches['status'] === 'checked';
            $song->id = $matches['id'] + 0;
            $song->title = html_entity_decode($matches['title'], ENT_QUOTES);
            $song->artist = html_entity_decode($matches['artist'], ENT_QUOTES);
            $trackList[] = $song;
        }

        return $trackList;
    }

    public function getSummary()
    {
        try {
            $crawler = new Crawler($this->html);
            $crawlerSummaryDOM = $crawler->filter('[property="v:summary"]');
            return trim(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $crawlerSummaryDOM->html()));
        } catch (\Exception $e) {
            return '';
        }
    }
}
