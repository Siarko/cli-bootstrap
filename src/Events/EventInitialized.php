<?php

namespace Siarko\CliBootstrap\Events;
use Siarko\Events\Api\EventInterface;

class EventInitialized implements EventInterface
{

    public function getData(): mixed
    {
        return null;
    }
}