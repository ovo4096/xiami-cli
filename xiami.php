#!/usr/bin/env php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Xiami\Console\Commands\TestCommand;
use Xiami\Console\Commands\FavoritesCommand;

$application = new Application();
$application->add(new TestCommand());
$application->add(new FavoritesCommand());
$application->run();
