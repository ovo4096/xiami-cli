<?php

namespace Xiami\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Xiami\Console\Grabber\FavoriteGrabber;

class FavoritesCommand extends Command
{
    protected function configure()
    {
        $this->setName('favorites')->setDescription('Output Favorites!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $songs = FavoriteGrabber::getSongs(2);
        $table = new Table($output);
        $table->setHeaders(['In Stock', 'ID', 'Name', 'Artists', 'Rate']);
        foreach ($songs as $song) {
            $artistNames = array_map(function ($artist) {
                return $artist->name;
            }, $song->artists);

            $table->addRow([
                $song->inStock ? 'Yes' : 'No',
                $song->id,
                $song->name,
                implode(', ', $artistNames),
                $song->rate
            ]);
        }
        $table->render();
    }
}
