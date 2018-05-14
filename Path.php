<?php

namespace AppBundle\Recommendation;

use function foo\func;

class Path
{
    private $raw;

    public function __construct(array $array)
    {
        $this->raw = $array;
    }

    public function start()
    {
        return $this->raw[0];
    }

    public function end()
    {
        return end($this->raw);
    }

    public function length()
    {
        return count($this->raw) >> 1;
    }

    public function distance() {
        $arr = array_filter($this->raw, function ($v, $i) {
            return $i && 1;
        });

        return array_reduce($arr, function($p, $c) {
            return $p + $c->getDistance();
        }, 0);
    }
}