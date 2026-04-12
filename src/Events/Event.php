<?php

namespace Siarko\CliBootstrap\Events;

class Event implements EventInterface
{
    public function __construct(
        private readonly mixed $data = null
    ){}

    public function getData(): mixed
    {
        return $this->data;
    }
}