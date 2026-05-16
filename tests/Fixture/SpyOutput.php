<?php

namespace Silly\Test\Fixture;

use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class SpyOutput extends Output implements OutputInterface
{
    public $output;

    protected function doWrite(string $message, bool $newline): void
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
