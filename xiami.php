#!/usr/bin/env php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Xiami\Console\Command\LoginCommand;
use Xiami\Console\Command\LoginoutCommand;
use Xiami\Console\Command\SongCommand;
use Xiami\Console\Command\AlbumCommand;
use Xiami\Console\Command\CollectionCommand;

$application = new Application();
$application->add(new LoginCommand());
$application->add(new LoginoutCommand());
$application->add(new SongCommand());
$application->add(new AlbumCommand());
$application->add(new CollectionCommand());
$application->run();
