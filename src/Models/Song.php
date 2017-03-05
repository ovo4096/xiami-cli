<?php

namespace Xiami\Console\Models;

class Song
{
    const RATE_NULL = 0;
    const RATE_LOWER = 1;
    const RATE_LOW = 2;
    const RATE_MEDIUM = 3;
    const RATE_HIGH = 4;
    const RATE_HIGHER = 5;

    public $name;
    public $rate;
    public $artists = array();
}
