<?php

namespace Xiami\Console\Grabber;

use GuzzleHttp\Client;

abstract class Grabber
{
    private static $client;

    protected static function getClient()
    {
        if (is_null(self::$client)) {
            self::$client = new Client(['base_uri' => 'http://www.xiami.com/']);
        }
        return self::$client;
    }
}
