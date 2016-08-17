<?php

namespace JsPhpize\Nodes;

class Main extends Block
{
    protected $helperPrefix;

    public function __construct($helperPrefix, $parentheses = null)
    {
        $this->helperPrefix = $helperPrefix;
        parent::__construct('main', $parentheses);
    }

    public function addDependencies($dependencies)
    {
        foreach ($dependencies as $varname => $dependency) {
            array_unshift($this->nodes, '$GLOBALS["' . $this->helperPrefix . $varname . '"] = ' . $dependency);
        }
    }
}
