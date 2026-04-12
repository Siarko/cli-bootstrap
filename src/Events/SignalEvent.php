<?php

namespace Siarko\CliBootstrap\Events;

use Siarko\Events\Api\EventInterface;

class SignalEvent implements EventInterface
{

    public function __construct(private readonly int $signal){}

    public function getData(): int
    {
        return $this->signal;
    }
}