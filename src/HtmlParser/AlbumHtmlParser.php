<?php
namespace Xiami\Console\HtmlParser;

use Symfony\Component\DomCrawler\Crawler;

class AlbumHtmlParser extends HtmlParser
{
    public function getTags()
    {
        $crawler = new Crawler($this->html);
        $crawlerTagDOMs = $crawler->filter('#album_info table tr');
        $tags = [];
        foreach ($crawlerTagDOMs as $tagDOM) {
            $crawlerTagDOM = new Crawler($tagDOM);
            switch ($crawlerTagDOM->children()->eq(0)->text()) {
                case '艺人：':
                    $tags['Artist'] = trim($crawlerTagDOM->children()->eq(1)->text());
                    break;
                case '语种：':
                    $tags['Language'] = trim($crawlerTagDOM->children()->eq(1)->text());
                    break;
                case '唱片公司：':
                    $tags['Release Date'] = trim($crawlerTagDOM->children()->eq(1)->text());
                    break;
                case '发行时间：':
                    $tags['Publisher'] = trim($crawlerTagDOM->children()->eq(1)->text());
                    break;
                case '专辑类别：':
                    $tags['Genre'] = trim($crawlerTagDOM->children()->eq(1)->text());
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
        $tags['Title'] = html_entity_decode($matches[0], ENT_QUOTES);
        return $tags;
    }

    public function getSongs()
    {
    }
}
