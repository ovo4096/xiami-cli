<?php

namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Xiami\Console\Grabber\FavoriteGrabber;
use Xiami\Console\Formatter\SongFormatter;

class FavoriteCommand extends Command
{
    protected function configure()
    {
        $this->setName('favorite')->setDescription('Output Favorites!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $songs = FavoriteGrabber::getSongs(2);
        $table = new Table($output);
        $table->setHeaders(['In Stock', 'Name', 'Artists', 'Rate']);

        foreach ($songs as $song) {
            $songFormatter = new SongFormatter($song);

            $table->addRow([
                $song->inStock ? 'Yes' : 'No',
                $song->name,
                $songFormatter->artistsToString(),
                $songFormatter->rateToString()
            ]);
        }
        $table->render();
    }
}
