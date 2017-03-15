<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use GuzzleHttp\Client;
use Xiami\Console\Style\AwesomeStyle;
use Xiami\Console\Model\Song;
use Xiami\Console\Exception\GetPlaylistJsonException;
use Xiami\Console\Exception\DownloadSongException;

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
                    'Song id'
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
            ->setDescription('Show information or download of song');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new AwesomeStyle($input, $output);
        $id = $input->getArgument('id');
        $downloadPath = $input->getOption('download');
        $downloadQuality = $input->getOption('quality');
        try {
            $song = Song::get($id);
            if ($downloadPath === null) {
                $this->show($song, $io);
                return;
            }
            $this->download($song, $downloadPath, $downloadQuality, $io, $output);
        } catch (GetPlaylistJsonException $e) {
            $io->error($e->getMessage());
        }
    }

    protected function show(Song $song, OutputStyle $io)
    {
        $io->title($song->title);

        $io->description([
            [
                '<info>Id</info>:',
                $song->id
            ]
        ]);

        $list = [];
        if (!empty($song->artist)) {
            $list[] = [
                '<info>Artist</info>:',
                $song->artist
            ];
        }
        if (!empty($song->lyricist)) {
            $list[] = [
                '<info>Lyricist</info>:',
                $song->lyricist
            ];
        }
        if (!empty($song->composer)) {
            $list[] = [
                '<info>Composer</info>:',
                $song->composer
            ];
        }
        if (!empty($song->arranger)) {
            $list[] = [
                '<info>Arranger</info>:',
                $song->arranger
            ];
        }
        if (count($list) !== 0) {
            $io->description($list);
        }

        $list = [];
        if (!empty($song->albumId)) {
            $list[] = [
                '<info>Album Id</info>:',
                $song->albumId
            ];
        }
        if (!empty($song->albumTitle)) {
            $list[] = [
                '<info>Album Title</info>:',
                $song->albumTitle
            ];
        }
        if (count($list) !== 0) {
            $io->description($list);
        }

        $io->section('Downloads');
        if (isset($song->audioUrls[Song::LOSSLESS_QUALITY])) {
            $io->text('<info>Lossless Quality</info>');
            $io->listing($song->audioUrls['LOSSLESS']);
        }
        if (isset($song->audioUrls[Song::HIGH_QUALITY])) {
            $io->text('<info>High Quality</info>');
            $io->listing($song->audioUrls['HIGH']);
        }
        if (isset($song->audioUrls[Song::LOW_QUALITY])) {
            $io->text('<info>Low Quality</info>');
            $io->listing($song->audioUrls['LOW']);
        }
        if (!empty($song->lyricsUrl)) {
            $io->text('<info>Lyrics</info>');
            $io->listing([$song->lyricsUrl]);
        }
    }

    protected function download(Song $song, $path, $quality, OutputStyle $io, OutputInterface $output)
    {
        $quality = strtoupper($quality);
        $filePath = $path . '/' . $song->title . ' - ' . $song->artist;
        switch ($quality) {
            case Song::LOSSLESS_QUALITY:
                if (isset($song->audioUrls[Song::LOSSLESS_QUALITY][0])) {
                    $url = $song->audioUrls[Song::LOSSLESS_QUALITY][0];
                    break;
                }
            case Song::HIGH_QUALITY:
                if (isset($song->audioUrls[Song::HIGH_QUALITY][0])) {
                    $url = $song->audioUrls[Song::HIGH_QUALITY][0];
                    break;
                }
            case Song::LOW_QUALITY:
                if (isset($song->audioUrls[Song::LOW_QUALITY][0])) {
                    $url = $song->audioUrls[Song::LOW_QUALITY][0];
                    break;
                }
                throw new DownloadSongException('No audio files available');
            default:
                throw new DownloadSongException('Unknown audio quality options');
        }
        $matches = [];
        preg_match('/.*(?<ext>\..*?)(?=\?)/', $url, $matches);
        $filePath .= $matches['ext'];
        $client = new Client();
        $before = true;
        $progressBar = null;
        $response = $client->get($url, [
            'progress' => function ($totalSize, $currentSize) use ($output, &$before, &$progressBar, $song) {
                if ($totalSize > 0 && $before) {
                    $progressBar = new ProgressBar($output, $totalSize);
                    $progressBar->setMessage('<info>' . $song->title . ' - ' . $song->artist . '</info>', 'filename');
                    $progressBar->setMessage(round($currentSize / 1024 / 1024, 2) . 'MiB', 'current_size');
                    $progressBar->setFormat(' %filename% %current_size% [%bar%] %percent:3s%%');
                    $before = false;
                }
                if (!$before) {
                    $progressBar->setMessage(round($currentSize / 1024 / 1024, 2) . 'MiB', 'current_size');
                    $progressBar->setProgress($currentSize);
                }
            }
        ]);
        file_put_contents($filePath, $response->getBody());
        $progressBar->finish();
        $io->newLine();
    }
}
