<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Xiami\Console\Style\AwesomeStyle;
use Xiami\Console\Model\Collection;
use Xiami\Console\Exception\GetPlaylistJsonException;

class CollectionCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('collection')
            ->setDefinition([
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'Collection ID'
                )
            ])
            ->setDescription('Show information or download of collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new AwesomeStyle($input, $output);
        $id = $input->getArgument('id');
        try {
            $collection = Collection::get($id);
            $io->title($collection->title);

            $io->description([
                [
                    '<info>Id</>:', 
                    $collection->id
                ]
            ]);

            $list = [];
            if (isset($collection->maker)) {
                $list[] = [
                    '<info>Maker</>:', 
                    $collection->maker
                ];
            }
            if (isset($collection->updateDate)) {
                $list[] = [
                    '<info>Update Date</>:', 
                    $collection->updateDate
                ];
            }
            if (isset($collection->tags)) {
                $list[] = [
                    '<info>Tags</>:', 
                    implode(', ', $collection->tags)
                ];
            }
            $io->description($list);

            if (!empty($collection->introduction)) {
                $io->section('Introduction');
                $io->writeln($collection->introduction);
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
