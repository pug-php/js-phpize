<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parse\Exception;

class Instruction extends Node
{
    /**
     * @var array
     */
    protected $instructions;

    public function add($instruction)
    {
        if (!is_object($instruction)) {
            throw new Exception('An instance of Assignation or Value was expected, ' . gettype($instruction) . ' value type given.', 10);
        }

        if (!$instruction instanceof Assignation
        && !$instruction instanceof Value) {
            throw new Exception('An instance of Assignation or Value was expected, ' . get_class($instruction) . ' instance given.', 10);
        }

        $this->instructions[] = $instruction;
    }
}
