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

    /**
     * @var array
     */
    protected $localVariables;

    public function __construct($type, $parentheses = null)
    {
        $this->type = $type;
        $this->nodes = array();
        $this->parentheses = $parentheses;
    }

    public function let($variable, $prefix)
    {
        $this->addNodes(
            '$name = ' . var_export(ltrim($variable, '$'), true),
            '$names = array()'
        );
        $while = new self('while', '($prev = $name) && ($name = "' . $prefix . 'l_" . $name) && isset($$prev)');
        $while->addNode('$names[] = array($name, $prev)');
        $this->addNode($while);
        $while = new self('while', '$data = array_pop($names)');
        $while->addNodes(
            'list($name, $prev) = $data',
            '$$name = $$prev'
        );
        $this->addNode($while);
        $this->localVariables[] = func_get_args();
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
        if (count($this->localVariables)) {
            $localVariables = 'array(' . implode(', ', array_map(function ($data) {
                return 'array(' . var_export($data[0], true) . ',' . var_export($data[1], true) . ')';
            }, $this->localVariables)) . ')';
            $foreach = new self('foreach', $localVariables . ' as $data');
            $while = new self('while', '($prev = $name) && ($name = $prefix . "l_" . $name) && isset($$prev)');
            $while->addNodes(
                '$$prev = $$name',
                'unset($$name)'
            );
            $foreach->addNodes(
                'list($name, $prefix) = $data',
                $while
            );
            $this->addNode($foreach);
        }

        return $this->nodes;
    }

    public function getHead()
    {
        return $this->type . ($this->parentheses === null ? '' : ' (' . $this->parentheses . ')');
    }
}
