<?php

namespace Xiami\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use Xiami\Console\Parser\FavoriteParser;

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

        $songs = FavoriteParser::Songs($html);

        $output->writeln(var_dump($songs));
    }
}
