<?php

namespace Siarko\CliBootstrap\Events;

interface ConsumerInterface
{

    public function consumeEvent(EventInterface $event);
}