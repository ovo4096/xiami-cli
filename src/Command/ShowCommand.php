<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Style\OutputStyle;
use GuzzleHttp\Client;
use Xiami\Console\Model\Song;
use Xiami\Console\Model\Album;
use Xiami\Console\Exception\GetPlaylistJsonException;
use Xiami\Console\Style\AwesomeStyle;

class ShowCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('show')
            ->setDefinition([
                new InputArgument(
                    'type',
                    InputArgument::REQUIRED,
                    'Download type'
                ),
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'Download type id'
                )
            ])
            ->setDescription('show description')
            ->setHelp('show help');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleType($input, $output);
    }

    protected function handleType(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $type = $input->getArgument('type');
        $io = new AwesomeStyle($input, $output);
        switch ($type) {
            case 'song':
                $this->handleTypeOfSong($id, $io);
                break;
            case 'album':
                $this->handleTypeOfAlbum($id, $io);
                break;
            case 'collection':
                $this->handleTypeOfCollection($id, $io);
                break;
            default:
                throw new InvalidArgumentException('Can not handle this type');
                break;
        }
    }

    protected function handleTypeOfSong($id, OutputStyle $io)
    {
        try {
            $song = Song::get($id);
            $io->title($song->tags['Title']);

            $dtList = [];
            foreach ($song->tags as $key => $value) {
                if ($key === 'Title') {
                    continue;
                }
                if (!empty($value)) {
                    $dtlist[] = array("<info>$key</>:", $value);
                }
            }
            $io->description($dtlist);

            $io->section('Downloads');

            if (isset($song->audioUrls['LOSSLESS'])) {
                $io->text('<info>Lossless Quality</>');
                $io->listing($song->audioUrls['LOSSLESS']);
            }
            if (isset($song->audioUrls['HIGH'])) {
                $io->text('<info>High Quality</>');
                $io->listing($song->audioUrls['HIGH']);
            }
            if (isset($song->audioUrls['LOW'])) {
                $io->text('<info>Low Quality</>');
                $io->listing($song->audioUrls['LOW']);
            }

            if (!empty($song->lyricsUrl)) {
                $io->text('<info>Lyrics</>');
                $io->listing([ $song->lyricsUrl ]);
            }
        } catch (GetPlaylistJsonException $e) {
            $io->error($e->getMessage());
        }
    }

    protected function handleTypeOfAlbum($id, OutputStyle $io)
    {
        try {
            $album = Album::get($id);
            $io->title($album->tags['Title']);

            $dtList = [];
            foreach ($album->tags as $key => $value) {
                if ($key === 'Title') {
                    continue;
                }
                if (!empty($value)) {
                    $dtlist[] = array("<info>$key</>:", $value);
                }
            }
            $io->description($dtlist);

            if (!empty($album->summary)) {
                $io->section('Summary');
                $io->writeln($album->summary);
            }

            if (count($album->trackList) !== 0) {
                $io->section('Track List');
                $body = [];
                foreach ($album->trackList as $song) {
                    $body[] = [
                        $song->id,
                        $song->tags['Title'],
                        $song->tags['Artist'],
                        $song->hasCopyright ? 'Yes' : 'No'
                    ];
                }

                $io->table(
                    ['Id', 'Title', 'Artist', 'DL'],
                    $body
                );
            }
        } catch (GetPlaylistJsonException $e) {
            $io->error($e->getMessage());
        }
    }

    protected function handleTypeOfCollection($id, OutputStyle $io)
    {
    }
}
