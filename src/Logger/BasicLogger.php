<?php

namespace Siarko\CliBootstrap\Logger;

use Psr\Log\AbstractLogger;

class BasicLogger extends AbstractLogger
{
    public function __construct(private readonly string $logFile)
    {

    }
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $time = date('Y-m-d H:i:s');
        $message = $time."\t".$level.': '.$message.'\n';
        if($exception = $context['exception'] ?? false) {
            $message .= 'In file: '.$exception->getFile().':'.$exception->getLine()."\n";
            $message .= 'Stack trace:\n'.$exception->getTraceAsString()."\n";
        }

        file_put_contents($this->logFile, $message, FILE_APPEND);
    }
}