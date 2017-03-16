<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\OutputStyle;
use Xiami\Console\Helper\Helper;
use Xiami\Console\Style\AwesomeStyle;
use Xiami\Console\Model\Album;
use Xiami\Console\Exception\GetPlaylistJsonException;

class AlbumCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('album')
            ->setDefinition([
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'Album ID'
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
            ->setDescription('Show information or download of album');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new AwesomeStyle($input, $output);
        $id = $input->getArgument('id');
        $downloadPath = $input->getOption('download');
        $downloadQuality = $input->getOption('quality');
        try {
            $album = Album::get($id);
            if ($downloadPath === null) {
                $this->show($album, $io);
                return;
            }
            foreach ($album->trackList as $song) {
                Helper::download($song, $downloadPath, $downloadQuality, $io, $output);
            }
        } catch (GetPlaylistJsonException $e) {
            $io->error($e->getMessage());
        }
    }

    protected function show(Album $album, OutputStyle $io)
    {
        $io->title($album->title);

        $io->description([
            [
                '<info>Id</info>:',
                $album->id
            ]
        ]);

        $list = [];
        if (!empty($album->artist)) {
            $list[] = [
                '<info>Artist</info>:',
                $album->artist
            ];
        }
        if (!empty($album->language)) {
            $list[] = [
                '<info>Language</info>:',
                $album->language
            ];
        }
        if (!empty($album->publisher)) {
            $list[] = [
                '<info>Publisher</info>:',
                $album->publisher
            ];
        }
        if (!empty($album->releaseDate)) {
            $list[] = [
                '<info>Release Date</info>:',
                $album->releaseDate
            ];
        }
        if (!empty($album->genre)) {
            $list[] = [
                '<info>Genre</info>:',
                $album->genre
            ];
        }
        $io->description($list);

        if (!empty($album->summary)) {
            $io->section('Summary');
            $io->writeln($album->summary);
        }

        $io->section('Track List');
        $body = [];
        foreach ($album->trackList as $song) {
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
