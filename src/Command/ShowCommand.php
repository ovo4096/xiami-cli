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
            $io->title($song->name);
            
            $dtlist = [];
            if (!empty($song->albumName)) {
                $dtlist[] = array('<info>Album</>:', $song->albumName);
            }
            if (!empty($song->artistNames)) {
                $dtlist[] = array('<info>Artist</>:', $song->artistNames);
            }
            if (!empty($song->lyricistNames)) {
                $dtlist[] = array('<info>Lyricist</>:', $song->lyricistNames);
            }
            if (!empty($song->composerNames)) {
                $dtlist[] = array('<info>Composer</>:', $song->composerNames);
            }
            if (!empty($song->arrangerNames)) {
                $dtlist[] = array('<info>Arranger</>:', $song->arrangerNames);
            }
            $io->description($dtlist);

            $io->section('Download Links');
            if (isset($song->audioLinks['LOSSLESS'])) {
                $io->text('<info>Lossless Quality</>');
                $io->listing($song->audioLinks['LOSSLESS']);
            }
            if (isset($song->audioLinks['HIGH'])) {
                $io->text('<info>High Quality</>');
                $io->listing($song->audioLinks['HIGH']);
            }
            if (isset($song->audioLinks['LOW'])) {
                $io->text('<info>Low Quality</>');
                $io->listing($song->audioLinks['LOW']);
            }
            if (!empty($song->lyricLink)) {
                $io->text('<info>Lyric</>');
                $io->listing([ $song->lyricLink ]);
            }

            $io->newLine();
        } catch (GetPlaylistJsonException $e) {
            $io->error($e->getMessage());
        }
    }

    protected function handleTypeOfAlbum($id, OutputStyle $io)
    {
        try {
            $album = Album::get($id);
            var_dump($album);
            die();
        } catch (GetPlaylistJsonException $e) {
            $io->error($e->getMessage());
        }
    }

    protected function handleTypeOfCollection($id, OutputStyle $io)
    {
    }
}
