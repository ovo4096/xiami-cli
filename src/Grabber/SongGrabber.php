<?php
namespace Xiami\Console\Grabber;

class SongGrabber
{
    public static function GetFromPlaylistJsonBySongId($id)
    {
        $client = self::getClient();
    }
}
