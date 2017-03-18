<?php
namespace Xiami\Console\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Xiami\Console\Helper\Helper;
use Xiami\Console\HtmlParser\MySongsHtmlParser;
use Xiami\Console\Model\Song;
use Xiami\Console\Style\AwesomeStyle;

class MySongsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('my-songs')
            ->setDefinition([
                new InputOption('page', null, InputOption::VALUE_REQUIRED, 'Page number', 1),
                new InputOption('userid', null, InputOption::VALUE_REQUIRED, 'User id'),
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
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new AwesomeStyle($input, $output);
        $userId = $input->getOption('userid');
        $page = $input->getOption('page');
        $downloadPath = $input->getOption('download');
        $downloadQuality = $input->getOption('quality');
        $user = $this->getUserCache();
        if ($user === null && $userId === null) {
            $io->error('Please login or specify a user id');
            return;
        }
        $userId = $userId === null ? $user->id : $userId;
        $songs = [];
        $pageIndex = $page === 'all' ? 1 : $page;

        do {
            $html = (string)(new Client())->get("http://www.xiami.com/space/lib-song/u/$userId/page/$pageIndex")->getBody();
            $pageIndex++;
            $htmlParser = new MySongsHtmlParser($html);

            $currentPage = $htmlParser->getNumberOfCurrentPage();
            $totalPages = $htmlParser->getTotalPages();

            $currentPageSongs = $htmlParser->getSongs();
            array_push($songs, $currentPageSongs);

            if ($downloadPath !== null) {
                foreach ($currentPageSongs as $song) {
                    if ($song->hasCopyright) {
                        $song->merge(Song::get($song->id));
                    }
                    Helper::download($song, $downloadPath, $downloadQuality, $io, $output);
                }
                continue;
            }

            $songTableItems = [];
            foreach ($currentPageSongs as $song) {
                $songTableItems[] = [
                    $song->id,
                    $song->title,
                    $song->artist,
                    $song->rateToString(),
                    $song->hasCopyright ? 'Yes' : 'No',
                ];
            }
            $io->newLine();
            $io->table(
                ['Id', 'Title', 'Artist', 'Rate', 'DL'],
                $songTableItems
            );
            $io->text("Page $currentPage of $totalPages");
            $io->newLine();
        } while ($page === 'all' && $currentPage != $totalPages);
    }
}
