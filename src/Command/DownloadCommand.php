<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class DownloadCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('download')
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
            ->setDescription('download description')
            ->setHelp('download help');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

    protected function handleTypeOfAlbum($id)
    {
    }

    protected function handleTypeOfSong($id)
    {
    }

    protected function handleTypeOfCollection($id)
    {
    }
}
