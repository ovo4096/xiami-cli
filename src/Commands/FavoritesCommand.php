<?php

namespace Xiami\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;

class FavoritesCommand extends Command
{
    protected function configure()
    {
        $this->setName('favorites')->setDescription('Output Favorites!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('hello');
        $client = new Client(['base_uri' => 'http://www.xiami.com/']);
        $response = $client->get('space/lib-song/u/12119063/page/1');
        $content = (String)$response->getBody();
        preg_match_all(
            '/<tr\sdata-needpay="(\d)"[\s\S]*?data-playstatus="(\d)"[\s\S]*?data-downloadstatus="(\d)"[\s\S]*?data-json="(.*?)"[\s\S]*?value="(\d+)"[\s\S]*?(checked|disabled)[\s\S]*?title="(.*?)"[\s\S]*?href="(.*)"[\s\S]*?(?:<a.*href="(.*?)".*(?=\s-)|(?=-))[\s\S]*?-[\s\S]*?(?=<)(?:<a.*?href="(.*?)".*?e="(.*?)".*?\/a>\s?)*/',
            $content,
            $songs
        );
        $output->writeln(var_dump($songs));
    }
}
