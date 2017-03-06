<?php

namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use Xiami\Console\Grabber\PageGrabber;
use Xiami\Console\Parser\FavoriteSongPageParser;
use Xiami\Console\Formatter\SongFormatter;

class FavoriteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('favorite')
            ->setDefinition([
                new InputArgument('type', InputArgument::OPTIONAL, 'The favorite type', 'songs'),
                new InputOption('page', 'p', InputOption::VALUE_REQUIRED, 'Page number', '1'),
            ])
            ->setDescription('Show your favorite songs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parser = new FavoriteSongPageParser(
            PageGrabber::getFavoriteSongPage(1)
        );

        $table = new Table($output);
        $table->setHeaders(['In Stock', 'Name', 'Artists', 'Rate']);
        foreach ($parser->getSongs() as $song) {
            $songFormatter = new SongFormatter($song);

            $table->addRow([
                $song->inStock ? 'Yes' : 'No',
                $song->name,
                $songFormatter->artistsToString(),
                $songFormatter->rateToString()
            ]);
        }
        $table->render();
        $output->writeln('Page ' . $parser->getNumberOfCurrentPage() . ' of ' . $parser->getTotalPages());
    }
}
