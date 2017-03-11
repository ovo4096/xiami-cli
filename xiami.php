#!/usr/bin/env php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Xiami\Console\Command\TestCommand;
use Xiami\Console\Command\ShowCommand;
use Xiami\Console\Command\FavoriteCommand;
use Xiami\Console\Command\LoginCommand;
use Xiami\Console\Command\LoginoutCommand;
use Xiami\Console\Command\DownloadCommand;

$application = new Application();
$application->add(new TestCommand());
$application->add(new FavoriteCommand());
$application->add(new LoginCommand());
$application->add(new LoginoutCommand());
$application->add(new DownloadCommand());
$application->add(new ShowCommand());
$application->run();
