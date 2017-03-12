<?php
namespace Xiami\Console\HtmlParser;

abstract class HtmlParser
{
    protected $html;

    public function __construct($html)
    {
        $this->html = $html;
    }
}
