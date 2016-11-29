<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parser\Exception;

class Instruction extends Node
{
    /**
     * @var array
     */
    protected $instructions;

    public function add($instruction)
    {
        $this->instructions[] = $instruction;
    }
}
