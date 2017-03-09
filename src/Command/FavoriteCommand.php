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
                new InputArgument('type', InputArgument::OPTIONAL, 'The favorite type', 'song'),
                new InputOption('page', 'p', InputOption::VALUE_REQUIRED, 'Page number', '1'),
            ])
            ->setDescription('Show your favorite songs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');

        switch ($type) {
            case 'song':
                $this->handleSongType($input, $output);
                break;
            default:
                $output->writeln('<comment>I did nothing.</comment>');
                break;
        }
    }

    protected function handleSongType(InputInterface $input, OutputInterface $output)
    {
        $page = $input->getOption('page');

        $parser = new FavoriteSongPageParser(
            PageGrabber::getFavoriteSongPage($page)
        );

        $table = new Table($output);
        $table->setHeaders(['In Stock', 'Id', 'Name', 'Artists', 'Rate']);
        foreach ($parser->getSongs() as $song) {
            $songFormatter = new SongFormatter($song);

            $table->addRow([
                $song->inStock ? 'Yes' : 'No',
                $song->id,
                $song->name,
                $songFormatter->artistsToString(),
                $songFormatter->rateToString()
            ]);
        }
        $table->render();
        $output->writeln('Page ' . $parser->getNumberOfCurrentPage() . ' of ' . $parser->getTotalPages());
    }

    protected function getLocation($location)
    {
        $loc_2 = (int)substr($location, 0, 1);
        $loc_3 = substr($location, 1);
        $loc_4 = floor(strlen($loc_3) / $loc_2);
        $loc_5 = strlen($loc_3) % $loc_2;
        $loc_6 = array();
        $loc_7 = 0;
        $loc_8 = '';
        $loc_9 = '';
        $loc_10 = '';
        while ($loc_7 < $loc_5) {
            $loc_6[$loc_7] = substr($loc_3, ($loc_4+1)*$loc_7, $loc_4+1);
            $loc_7++;
        }
        $loc_7 = $loc_5;
        while ($loc_7 < $loc_2) {
            $loc_6[$loc_7] = substr($loc_3, $loc_4 * ($loc_7 - $loc_5) + ($loc_4 + 1) * $loc_5, $loc_4);
            $loc_7++;
        }
        $loc_7 = 0;
        while ($loc_7 < strlen($loc_6[0])) {
            $loc_10 = 0;
            while ($loc_10 < count($loc_6)) {
                $loc_8 .= isset($loc_6[$loc_10][$loc_7]) ? $loc_6[$loc_10][$loc_7] : null;
                $loc_10++;
            }
            $loc_7++;
        }
        $loc_9 = str_replace('^', 0, urldecode($loc_8));
        return $loc_9;
    }
}
