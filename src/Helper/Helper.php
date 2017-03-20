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
    private static function onceDownload(Song $song, $path, $quality, OutputStyle $io, OutputInterface $output)
    {
        if (!$song->hasCopyright) {
            $io->writeln(' <error>No copyright</error> <info>' . $song->title . ' - ' . $song->artist . '</info>');
            return;
        }
        $quality = strtoupper($quality);

        $title = preg_replace("/\\//", "\\", $song->title);
        $artist = preg_replace("/\\//", "\\", $song->artist);

        $filename = $title . ' - ' . $artist;

        if (strlen($filename) > 220) {
            $filename = md5($filename);
        }

        $filePath = $path . '/' . $filename;
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

        if (file_exists($filePath)) {
            $io->writeln(' <info>' . $song->title . ' - ' . $song->artist . '</info> 100%');
            return;
        }

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
            },
            'timeout' => 30
        ]);
        file_put_contents($filePath, $response->getBody());
        $progressBar->finish();
        $io->newLine();
    }

    public static function download(Song $song, $path, $quality, OutputStyle $io, OutputInterface $output) {

        try {
            self::onceDownload($song, $path, $quality, $io, $output);
        } catch (\Exception $e) {
            $io->newLine();
            $io->writeln(' <error>' . $e->getMessage() . '</error> <info>' . $song->title . ' - ' . $song->artist . '</info>');
            self::download($song, $path, $quality, $io, $output);
        }
    }

    public static function getLocation($location)
    {
        $loc_2 = (int)substr($location, 0, 1);
        $loc_3 = substr($location, 1);
        $loc_4 = floor(strlen($loc_3) / $loc_2);
        $loc_5 = strlen($loc_3) % $loc_2;
        $loc_6 = array();
        $loc_7 = 0;
        $loc_8 = '';
        $loc_9 = '';
        $loc_10 = '';
        while ($loc_7 < $loc_5) {
            $loc_6[$loc_7] = substr($loc_3, ($loc_4 + 1) * $loc_7, $loc_4 + 1);
            $loc_7++;
        }
        $loc_7 = $loc_5;
        while ($loc_7 < $loc_2) {
            $loc_6[$loc_7] = substr($loc_3, $loc_4 * ($loc_7 - $loc_5) + ($loc_4 + 1) * $loc_5, $loc_4);
            $loc_7++;
        }
        $loc_7 = 0;
        while ($loc_7 < strlen($loc_6[0])) {
            $loc_10 = 0;
            while ($loc_10 < count($loc_6)) {
                $loc_8 .= isset($loc_6[$loc_10][$loc_7]) ? $loc_6[$loc_10][$loc_7] : null;
                $loc_10++;
            }
            $loc_7++;
        }
        $loc_9 = str_replace('^', 0, urldecode($loc_8));
        return $loc_9;
    }
}