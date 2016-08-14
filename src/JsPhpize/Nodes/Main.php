<?php

namespace JsPhpize\Nodes;

class Main extends Block
{
    public function __construct($parentheses = null)
    {
        parent::__construct('main', $parentheses);
    }

    public function addDependencies($dependencies)
    {
        foreach ($dependencies as $varname => $dependency) {
            array_unshift($this->nodes, '$GLOBALS["__jp_' . $varname . '"] = ' . $dependency);
        }
    }
}
