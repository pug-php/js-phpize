<?php

namespace JsPhpize\Nodes;

class Block
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $parentheses;

    /**
     * @var array
     */
    protected $nodes;

    public function __construct($type, $parentheses = null)
    {
        $this->type = $type;
        $this->nodes = array();
        $this->parentheses = $parentheses;
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

    public function setParentheses($parentheses)
    {
        $this->parentheses = $parentheses;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getHead()
    {
        return $this->type . ($this->parentheses === null ? '' : ' (' . $this->parentheses . ')');
    }
}
