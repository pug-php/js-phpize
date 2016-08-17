<?php

namespace JsPhpize\Nodes;

class Parenthesis
{
    /**
     * @var array
     */
    protected $nodes;

    public function __construct()
    {
        $this->nodes = array();
    }

    public function addNodes($nodes)
    {
        $nodes = array_filter(is_array($nodes) ? $nodes : func_get_args());
        $this->nodes = array_merge($this->nodes, $nodes);
    }

    public function addNode()
    {
        $this->addNodes(func_get_args());
    }

    public function __toString()
    {
        return implode(' ', $this->nodes);
    }
}
