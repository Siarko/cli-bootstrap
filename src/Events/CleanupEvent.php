<?php

namespace Siarko\CliBootstrap\Events;

use Siarko\Events\Api\EventInterface;

class CleanupEvent implements EventInterface
{

    public function getData(): mixed
    {
        return null;
    }
}