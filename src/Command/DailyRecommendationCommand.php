<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Xiami\Console\Exception\GetPlaylistJsonException;
use Xiami\Console\Helper\Helper;
use Xiami\Console\Style\AwesomeStyle;
use Xiami\Console\Model\DailyRecommendationCollection;

class DailyRecommendationCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('daily-recommendation')
            ->setDefinition([
                new InputOption(
                    'download',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Download path'
                ),
                new InputOption(
                    'quality',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Download audio quality',
                    'high'
                )
            ])
            ->setDescription('Get the daily recommendation songs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new AwesomeStyle($input, $output);
        $downloadPath = $input->getOption('download');
        $downloadQuality = $input->getOption('quality');
        $user = $this->getUserCache();
        try {
            $collection = DailyRecommendationCollection::get($user);
            if ($downloadPath === null) {
                $this->show($collection, $io);
                return;
            }
            foreach ($collection->trackList as $song) {
                Helper::download($song, $downloadPath, $downloadQuality, $io, $output);
            }
        } catch (GetPlaylistJsonException $e) {
            $io->error($e->getMessage());
        }
    }

    protected function show(DailyRecommendationCollection $collection, OutputStyle $io)
    {
        $io->newLine();
        $body = [];
        foreach ($collection->trackList as $song) {
            $body[] = [
                $song->id,
                $song->title,
                $song->artist,
                $song->hasCopyright ? 'Yes' : 'No'
            ];
        }
        $io->table(
            ['Id', 'Title', 'Artist', 'DL'],
            $body
        );
    }
}