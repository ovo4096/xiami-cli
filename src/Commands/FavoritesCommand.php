<?php

namespace Xiami\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use Xiami\Console\Models\Song;
use Xiami\Console\Models\Artist;

class FavoritesCommand extends Command
{
    protected function configure()
    {
        $this->setName('favorites')->setDescription('Output Favorites!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client(['base_uri' => 'http://www.xiami.com/']);
        $response = $client->get('space/lib-song/u/12119063/page/1');
        $html = (String)$response->getBody();

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
                $artist->name = $matche['name'];
                $song->artists[] = $artist;
            }

            $songs[] = $song;
        }

        $output->writeln(var_dump($songs));
    }
}
