<?php

namespace Siarko\CliBootstrap\Events;

use Siarko\Events\Api\EventInterface;

class ExitEvent implements EventInterface
{
    private array $errorData;

    public function __construct($errorData = [])
    {
        $this->errorData = $errorData;
    }

    public function getData(): mixed
    {
        return $this->errorData;
    }
}