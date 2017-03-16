<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\OutputStyle;
use Xiami\Console\Helper\Helper;
use Xiami\Console\Style\AwesomeStyle;
use Xiami\Console\Model\Collection;
use Xiami\Console\Exception\GetPlaylistJsonException;

class CollectionCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('collection')
            ->setDefinition([
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'Collection id'
                ),
                new InputOption(
                    'download',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'To download this song to the specified path'
                ),
                new InputOption(
                    'quality',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Specify the download audio quality',
                    'high'
                )
            ])
            ->setDescription('Show information or download of collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new AwesomeStyle($input, $output);
        $id = $input->getArgument('id');
        $downloadPath = $input->getOption('download');
        $downloadQuality = $input->getOption('quality');
        try {
            $collection = Collection::get($id);
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

    protected function show(Collection $collection, OutputStyle $io)
    {
        $io->title($collection->title);

        $io->description([
            [
                '<info>Id</info>:',
                $collection->id
            ]
        ]);

        $list = [];
        if (isset($collection->maker)) {
            $list[] = [
                '<info>Maker</info>:',
                $collection->maker
            ];
        }
        if (isset($collection->updateDate)) {
            $list[] = [
                '<info>Update Date</info>:',
                $collection->updateDate
            ];
        }
        if (isset($collection->tags)) {
            $list[] = [
                '<info>Tags</info>:',
                implode(', ', $collection->tags)
            ];
        }
        $io->description($list);

        if (!empty($collection->introduction)) {
            $io->section('Introduction');
            $io->writeln($collection->introduction);
        }

        $io->section('Track List');
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
