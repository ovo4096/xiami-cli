<?php
namespace Xiami\Console\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

abstract class Command extends SymfonyCommand
{
    protected $cache;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->cache = new FilesystemAdapter('xiami-cli');
    }
}
