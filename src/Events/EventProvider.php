<?php

namespace Siarko\CliBootstrap\Events;

interface EventProvider
{

    public function getEvent(): ?EventInterface;

}