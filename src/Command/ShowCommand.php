<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use GuzzleHttp\Client;

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
        $client = new Client(
            ['base_uri' => 'http://www.xiami.com/']
        );

        $response = $client->get("song/playlist/id/$id/object_name/default/object_id/0/cat/json");
        $json = json_decode((string) $response->getBody());

        if (!empty($json->message) || !$json->status) {
            $io->error($json->message);
            return;
        }

        $io->text('<info>' . $json->data->trackList[0]->songName . '</info>');
        $io->newLine();

        if (!empty($json->data->trackList[0]->album_name)) {
            $io->text('所属专辑: ' . $json->data->trackList[0]->album_name);
        }

        if (!empty($json->data->trackList[0]->singers)) {
            $io->text('演唱者: ' . $json->data->trackList[0]->singers);
        }

        if (!empty($json->data->trackList[0]->songwriters)) {
            $io->text('作词: ' . $json->data->trackList[0]->songwriters);
        }

        if (!empty($json->data->trackList[0]->composer)) {
            $io->text('作曲: ' . $json->data->trackList[0]->composer);
        }

        if (!empty($json->data->trackList[0]->arrangement)) {
            $io->text('编曲: ' . $json->data->trackList[0]->arrangement);
        }
        
        $io->newLine();
    }

    protected function handleTypeOfAlbum($id, SymfonyStyle $io)
    {
    }

    protected function handleTypeOfCollection($id, SymfonyStyle $io)
    {
    }
}
