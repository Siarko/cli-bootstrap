<?php

namespace Siarko\CliBootstrap\Exception;

class FabricatedException extends \RuntimeException
{

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    public function setLine(int $line): void
    {
        $this->line = $line;
    }
}