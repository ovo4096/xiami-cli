<?php

namespace Xiami\Console\Grabber;

use Xiami\Console\Parser\FavoriteSongPageParser;

class PageGrabber extends Grabber
{
    public static function getFavoriteSongPage($page)
    {
        $response = self::getClient()->get("space/lib-song/u/5627589/page/$page");
        return (String)$response->getBody();
    }
}
