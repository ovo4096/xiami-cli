<?php
namespace Xiami\Console\Parser;

abstract class Parser
{
    protected $html;

    public function __construct($html)
    {
        $this->html = $html;
    }
}
