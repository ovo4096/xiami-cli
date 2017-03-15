<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
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
                )
            ])
            ->setDescription('Show information or download of album');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new AwesomeStyle($input, $output);
        $id = $input->getArgument('id');
        try {
            $album = Album::get($id);
            $io->title($album->title);

            $io->description([
                [
                    '<info>Id</>:', 
                    $album->id
                ]
            ]);

            $list = [];
            if (!empty($album->artist)) {
                $list[] = [
                    '<info>Artist</>:', 
                    $album->artist
                ];
            }
            if (!empty($album->language)) {
                $list[] = [
                    '<info>Language</>:', 
                    $album->language
                ];
            }
            if (!empty($album->publisher)) {
                $list[] = [
                    '<info>Publisher</>:', 
                    $album->publisher
                ];
            }
            if (!empty($album->releaseDate)) {
                $list[] = [
                    '<info>Release Date</>:', 
                    $album->releaseDate
                ];
            }
            if (!empty($album->genre)) {
                $list[] = [
                    '<info>Genre</>:', 
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
        } catch (GetPlaylistJsonException $e) {
            $io->error($e->getMessage());
        }
    }
}
