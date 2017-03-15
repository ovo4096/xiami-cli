<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Xiami\Console\Style\AwesomeStyle;
use Xiami\Console\Model\Song;
use Xiami\Console\Exception\GetPlaylistJsonException;

class SongCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('song')
            ->setDefinition([
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'Song ID'
                )
            ])
            ->setDescription('Show information or download of song');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new AwesomeStyle($input, $output);
        $id = $input->getArgument('id');
        try {
            $song = Song::get($id);
            $io->title($song->title);

            $io->description([
                [
                    '<info>Id</>:',
                    $song->id
                ]
            ]);

            $list = [];
            if (!empty($song->artist)) {
                $list[] = [
                    '<info>Artist</>:',
                    $song->artist
                ];
            }
            if (!empty($song->lyricist)) {
                $list[] = [
                    '<info>Lyricist</>:',
                    $song->lyricist
                ];
            }
            if (!empty($song->composer)) {
                $list[] = [
                    '<info>Composer</>:',
                    $song->composer
                ];
            }
            if (!empty($song->arranger)) {
                $list[] = [
                    '<info>Arranger</>:',
                    $song->arranger
                ];
            }
            if (count($list) !== 0) {
                $io->description($list);
            }

            $list = [];
            if (!empty($song->albumId)) {
                $list[] = [
                    '<info>Album Id</>:',
                    $song->albumId
                ];
            }
            if (!empty($song->albumTitle)) {
                $list[] = [
                    '<info>Album Title</>:',
                    $song->albumTitle
                ];
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
}
