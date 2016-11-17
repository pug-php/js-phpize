<?php

namespace JsPhpize\Nodes;

abstract class Value extends Node
{
    /**
     * @var array
     */
    protected $before;

    /**
     * @var array
     */
    protected $after;

    public function getBefore()
    {
        return implode(' ', $this->before);
    }

    public function prepend($before)
    {
        array_unshift($this->before, $before);
    }

    public function getAfter()
    {
        return implode(' ', $this->after);
    }

    public function append($after)
    {
        $this->after[] = $after;
    }
}
