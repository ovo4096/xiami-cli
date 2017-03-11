<?php
namespace Xiami\Console\Grabber;

class SongGrabber extends Grabber
{
    public static function get($id)
    {
        $client = self::getClient();
    }
}
