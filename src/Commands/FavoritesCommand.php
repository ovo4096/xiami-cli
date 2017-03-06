<?php

namespace Xiami\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use Xiami\Console\Grabber\FavoriteGrabber;

class FavoritesCommand extends Command
{
    protected function configure()
    {
        $this->setName('favorites')->setDescription('Output Favorites!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $songs = FavoriteGrabber::getSongs(1);
        $output->writeln(var_dump($songs));
    }
}
