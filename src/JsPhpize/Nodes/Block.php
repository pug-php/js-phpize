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

    /**
     * @var array
     */
    protected $dependencies;

    public function __construct($type, $parentheses = null)
    {
        $this->type = $type;
        $this->nodes = array();
        $this->dependencies = array();
        $this->parentheses = $parentheses;
    }

    public function addDependencies($dependencies)
    {
        $this->dependencies = array_merge($this->dependencies, $dependencies);
    }

    public function popDependencies()
    {
        $dependencies = $this->dependencies;
        $this->dependencies = array();

        return $dependencies;
    }

    public function let($variable, $prefix)
    {
        $this->addInstructions(
            '$name = ' . var_export(ltrim($variable, '$'), true),
            '$names = array()'
        );
        $while = new self('while', '($prev = $name) && ($name = "' . $prefix . 'l_" . $name) && isset($$prev)');
        $while->addInstruction('$names[] = array($name, $prev)');
        $this->addNode($while);
        $while = new self('while', '$data = array_pop($names)');
        $while->addInstructions(
            'list($name, $prev) = $data',
            '$$name = $$prev'
        );
        $this->addNode($while);
        $this->localVariables[] = func_get_args();
    }

    public function addNodes($nodes)
    {
        foreach (array_filter(is_array($nodes) ? $nodes : func_get_args()) as $node) {
            if (is_string($node) && is_string(end($this->nodes))) {
                $this->nodes[count($this->nodes) - 1] .= ' ' . $node;
            } else {
                $this->nodes[] = $node;
            }
        }
    }

    public function addNode()
    {
        $this->addNodes(func_get_args());
    }

    public function addInstructions($instructions)
    {
        foreach (is_array($instructions) ? $instructions : func_get_args() as $instruction) {
            $this->addNode(
                $instruction,
                new NodeEnd()
            );
        }
    }

    public function addInstruction()
    {
        $this->addInstructions(func_get_args());
    }

    public function setParentheses($parentheses)
    {
        $this->parentheses = $parentheses;
    }

    public function getNodes()
    {
        foreach ($this->dependencies as $varname => $dependency) {
            array_unshift($this->nodes, '$GLOBALS["' . $this->helperPrefix . $varname . '"] = ' . $dependency, new NodeEnd());
        }
        if (count($this->localVariables)) {
            $localVariables = 'array(' . implode(', ', array_map(function ($data) {
                return 'array(' . var_export($data[0], true) . ',' . var_export($data[1], true) . ')';
            }, $this->localVariables)) . ')';
            $foreach = new self('foreach', $localVariables . ' as $data');
            $while = new self('while', '($prev = $name) && ($name = $prefix . "l_" . $name) && isset($$prev)');
            $while->addInstructions(
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
