<?php

namespace Siarko\CliBootstrap\Events;

use Siarko\CliBootstrap\Events\EventInterface;

class CleanupEvent implements EventInterface
{

    public function getData(): mixed
    {
        return null;
    }
}