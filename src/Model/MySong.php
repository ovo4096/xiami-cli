<?php
namespace Xiami\Console\Model;

class MySong extends Song
{
    const RATE_NONE = 0;
    const RATE_LOWER = 1;
    const RATE_LOW = 2;
    const RATE_MEDIUM = 3;
    const RATE_HIGH = 4;
    const RATE_HIGHER = 5;
    public $rate = self::RATE_NONE;

    public function rateToString()
    {
        $rateString = '☆☆☆☆☆';
        switch ($this->rate) {
            case self::RATE_LOWER:
                $rateString = '★☆☆☆☆';
                break;
            case self::RATE_LOW:
                $rateString = '★★☆☆☆';
                break;
            case self::RATE_MEDIUM:
                $rateString = '★★★☆☆';
                break;
            case self::RATE_HIGH:
                $rateString = '★★★★☆';
                break;
            case self::RATE_HIGHER:
                $rateString = '★★★★★';
                break;
            default:
                break;
        }
        return $rateString;
    }

    public function merge($song)
    {
        parent::merge($song);

        if (!isset($this->rate) && isset($song->rate)) {
            $this->rate = $song->rate;
        }
    }
}
