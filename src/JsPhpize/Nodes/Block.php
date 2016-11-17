<?php

namespace JsPhpize\Nodes;

class Block extends Node
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
    protected $instructions;

    /**
     * @var boolean
     */
    protected $inInstruction;

    public function __construct($type, $parentheses = null)
    {
        $this->type = $type;
        $this->instructions = array();
        $this->inInstruction = false;
        $this->parentheses = $parentheses;
    }

    public function addInstructions($instructions)
    {
        $instructions = is_array($instructions) ? $instructions : func_get_args();
        if (count($instructions)) {
            if (!$this->inInstruction) {
                $this->inInstruction = true;
                $this->instructions[] = new Instruction();
            }
            foreach ($instructions as $instruction) {
                $this->instructions[count($this->instructions) - 1]->add($instruction);
            }
        }
    }

    public function addInstruction()
    {
        $this->addInstructions(func_get_args());
    }

    public function endInstruction()
    {
        $this->inInstruction = false;
    }

    public function setParentheses(Parenthesis $parentheses)
    {
        $this->parentheses = $parentheses;
    }
}
