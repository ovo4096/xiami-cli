<?php
namespace Xiami\Console\Helper;

use Symfony\Component\Console\Helper\ProgressBar;
use GuzzleHttp\Client;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Xiami\Console\Exception\DownloadSongException;
use Xiami\Console\Model\Song;

class Helper
{
    public static function download(Song $song, $path, $quality, OutputStyle $io, OutputInterface $output)
    {
        if (!$song->hasCopyright) {
            $io->writeln(' <error>No copyright</error> <info>' . $song->title . ' - ' . $song->artist . '</info>');
            return;
        }
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
                    $progressBar->setFormat(' [%bar%] %filename% %percent:3s%%');
                    $before = false;
                }
                if (!$before) {
                    $progressBar->setProgress($currentSize);
                }
            }
        ]);
        file_put_contents($filePath, $response->getBody());
        $progressBar->finish();
        $io->newLine();
    }
}