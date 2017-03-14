<?php
namespace Xiami\Console\HtmlParser;

abstract class HtmlParser
{
    protected $html;

    public function __construct($html)
    {
        $this->html = $html;
    }

    public static function formatHtmlTextareaToConsoleTextblock($html)
    {
        return html_entity_decode(
            html_entity_decode(
                preg_replace(
                    '/\<br(\s*)?\/?\>/i',
                    "\n",
                    preg_replace(
                        '/\<(?!br).*?(\s*)?\/?\>/i',
                        '',
                        preg_replace(
                            '/\s+/',
                            ' ',
                            trim($html)
                        )
                    )
                ),
                ENT_QUOTES
            )
        );
    }
}
