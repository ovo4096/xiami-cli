<?php
namespace Xiami\Console\HtmlParser;

use Symfony\Component\DomCrawler\Crawler;
use Xiami\Console\Model\Song;

class AlbumHtmlParser extends HtmlParser
{
    public function getTitle()
    {
        preg_match(
            '/h1[\s\S]*?>(?<title>[\s\S]*?)</',
            $this->html,
            $matches
        );
        if (!isset($matches['title'])) {
            return null;
        }
        return html_entity_decode(trim($matches['title']), ENT_QUOTES);
    }

    public function getArtist()
    {
        return $this->getTag('艺人：');
    }

    public function getLanguage()
    {
        return $this->getTag('语种：');
    }

    public function getPublisher()
    {
        return $this->getTag('唱片公司：');
    }

    public function getReleaseDate()
    {
        return $this->getTag('发行时间：');
    }

    public function getGenre()
    {
        return $this->getTag('专辑类别：');
    }

    public function getSummary()
    {
        preg_match(
            '/(?<=v:summary">)(?<summary>[\s\S]*?)(?=<\/span)/',
            $this->html,
            $matches
        );
        if (!isset($matches['summary'])) {
            return null;
        }

        return self::formatHtmlTextareaToConsoleTextblock($matches['summary']);
    }

    public function getTrackList()
    {
        $trackList = [];
        $crawler = new Crawler($this->html);
        
        $doms = $crawler->filter('#track_list tr[data-needpay]');
        foreach ($doms as $dom) {
            $dom = new Crawler($dom);
            $matches = [];
            preg_match(
                '/\s(?<status>checked|disabled).*?value="(?<id>\d*)"[\s\S]*?"">\s*(?<title>.*?)\s*<\/a>\s*(?<artist>.*?)\s*?</',
                $dom->html(),
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

    protected function getTag($name)
    {
        $crawler = new Crawler($this->html);
        $doms = $crawler->filter('#album_info table tr');
        foreach ($doms as $dom) {
            $dom = new Crawler($dom);
            if ($dom->children()->eq(0)->text() === $name) {
                return trim($dom->children()->eq(1)->text());
            }
        }
        return null;
    }
}
