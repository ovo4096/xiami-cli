#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Xiami\Console\Commands\TestCommand;
use Xiami\Console\Commands\FavoritesCommand;

$application = new Application();
$application->add(new TestCommand());
$application->add(new FavoritesCommand());
$application->run();
