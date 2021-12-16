<?php

namespace Silly\Test\Mock;

use Psr\Container\ContainerInterface;

class ArrayContainer implements ContainerInterface
{
    private $entries = [];

    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    public function get($id)
    {
        return $this->entries[$id];
    }

    public function has($id): bool
    {
        return isset($this->entries[$id]);
    }
}
