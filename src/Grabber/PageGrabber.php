<?php

namespace Xiami\Console\Grabber;

use Xiami\Console\Parser\FavoriteSongPageParser;

class PageGrabber extends Grabber
{
    public static function getFavoriteSongPage($page)
    {
        $response = self::getClient()->get("space/lib-song/u/12119063/page/$page");
        return (String)$response->getBody();
    }
}
