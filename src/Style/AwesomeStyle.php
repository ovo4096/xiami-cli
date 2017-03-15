<?php
namespace Xiami\Console\Style;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Table;

class AwesomeStyle extends SymfonyStyle
{
    public function description(array $rows)
    {
        $table = new Table($this);
        $table->setStyle('compact');
        $table->setRows($rows);
        $table->render();
        $this->newLine();
    }

    public function table(array $headers, array $rows)
    {
        $table = new Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();
        $this->newLine();
    }
}
