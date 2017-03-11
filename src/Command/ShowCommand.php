<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use GuzzleHttp\Client;
use Xiami\Console\Model\Song;
use Xiami\Console\Exception\GetPlaylistJsonException;

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
        $io = new SymfonyStyle($input, $output);
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

    protected function handleTypeOfSong($id, SymfonyStyle $io)
    {
        try {
            $song = Song::getFromPlaylistJsonById($id);
            $io->newLine();
            $io->table(
                array(
                    'Name',
                    'Album',
                    'Artist',
                    'Lyricist',
                    'Composer',
                    'Arranger'
                ),
                array(
                    array(
                        $song->name,
                        $song->albumName,
                        $song->artistNames,
                        $song->lyricistNames,
                        $song->composerNames,
                        $song->arrangerNames
                    ),
                )
            );
        } catch (GetPlaylistJsonException $e) {
            $io->error($e->getMessage());
        }
    }

    protected function handleTypeOfAlbum($id, SymfonyStyle $io)
    {
    }

    protected function handleTypeOfCollection($id, SymfonyStyle $io)
    {
    }
}
