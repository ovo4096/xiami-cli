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

    public function getUserCache()
    {
        $userCache = $this->cache->getItem('user');
        if (!$userCache->isHit()) {
            return null;
        }
        return $userCache->get();
    }

    public function setUserCache($user)
    {
        $userCache = $this->cache->getItem('user');
        $userCache->set($user);
        $this->cache->save($userCache);
    }

    public function deleteUserCache()
    {
        $this->cache->deleteItem('user');
    }
}
