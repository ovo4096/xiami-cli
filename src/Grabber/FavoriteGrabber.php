<?php

namespace Xiami\Console\Grabber;

use Xiami\Console\Parser\FavoriteParser;

class FavoriteGrabber extends Grabber
{
    public static function getSongs($page)
    {
        $response = self::getClient()->get("space/lib-song/u/12119063/page/$page");
        return FavoriteParser::getSongs((String)$response->getBody());
    }
}
