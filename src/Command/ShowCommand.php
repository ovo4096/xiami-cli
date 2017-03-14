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
use Xiami\Console\Model\Collection;
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
            $io->title($song->title);

            $io->description([
                ['<info>Id</>:', $song->id]
            ]);

            $list = [];
            if (isset($song->artist)) {
                $list[] = ['<info>Artist</>:', $song->artist];
            }
            if (isset($song->lyricist)) {
                $list[] = ['<info>Lyricist</>:', $song->lyricist];
            }
            if (isset($song->composer)) {
                $list[] = ['<info>Composer</>:', $song->composer];
            }
            if (isset($song->arranger)) {
                $list[] = ['<info>Arranger</>:', $song->arranger];
            }
            if (count($list) !== 0) {
                $io->description($list);
            }

            $list = [];
            if (isset($song->albumId)) {
                $list[] = ['<info>Album Id</>:', $song->albumId];
            }
            if (isset($song->albumTitle)) {
                $list[] = ['<info>Album Title</>:', $song->albumTitle];
            }
            if (count($list) !== 0) {
                $io->description($list);
            }

            $io->section('Downloads');
            if (isset($song->audioUrls[Song::LOSSLESS_QUALITY])) {
                $io->text('<info>Lossless Quality</>');
                $io->listing($song->audioUrls['LOSSLESS']);
            }
            if (isset($song->audioUrls[Song::HIGH_QUALITY])) {
                $io->text('<info>High Quality</>');
                $io->listing($song->audioUrls['HIGH']);
            }
            if (isset($song->audioUrls[Song::LOW_QUALITY])) {
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
            $io->title($album->title);

            $io->description([
                ['<info>Id</>:', $album->id]
            ]);

            $list = [];
            if (isset($album->artist)) {
                $list[] = ['<info>Artist</>:', $album->artist];
            }
            if (isset($album->language)) {
                $list[] = ['<info>Language</>:', $album->language];
            }
            if (isset($album->publisher)) {
                $list[] = ['<info>Publisher</>:', $album->publisher];
            }
            if (isset($album->releaseDate)) {
                $list[] = ['<info>Release Date</>:', $album->releaseDate];
            }
            if (isset($album->genre)) {
                $list[] = ['<info>Genre</>:', $album->genre];
            }
            $io->description($list);

            if (isset($album->summary)) {
                $io->section('Summary');
                $io->block($album->summary);
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

    protected function handleTypeOfCollection($id, OutputStyle $io)
    {
        try {
            $collection = Collection::get($id);
            $io->title($collection->title);

            $io->description([
                ['<info>Id</>:', $collection->id]
            ]);

            $list = [];
            if (isset($collection->maker)) {
                $list[] = ['<info>Maker</>:', $collection->maker];
            }
            if (isset($collection->updateDate)) {
                $list[] = ['<info>Update Date</>:', $collection->updateDate];
            }
            if (isset($collection->tags)) {
                $list[] = ['<info>Tags</>:', implode(', ', $collection->tags)];
            }
            $io->description($list);

            if (isset($collection->introduction)) {
                $io->section('Introduction');
                $io->block($collection->introduction);
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
        } catch (GetPlaylistJsonException $e) {
            $io->error($e->getMessage());
        }
    }
}
