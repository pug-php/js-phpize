<?php

namespace JsPhpize\Compiler;

use JsPhpize\JsPhpize;
use JsPhpize\Nodes\Block;
use JsPhpize\Nodes\Comment;

class Compiler
{
    /**
     * @var JsPhpize
     */
    protected $engine;

    public function __construct(JsPhpize $engine)
    {
        $this->engine = $engine;
    }

    protected function outputNode($node, $indent)
    {
        if ($node instanceof Block) {
            return $indent . $node->getHead() . "{\n" .
                $this->compile($node, '  ' . $indent) .
                $indent . "}\n";
        }
        if ($node instanceof Comment) {
            return $indent . $node . "\n";
        }
        $node = rtrim($node, ';');
        if (empty($node)) {
            return '';
        }

        return $indent . $node . ";\n";
    }

    public function compile(Block $block, $indent = '')
    {
        $output = '';
        $line = array();

        foreach ($block->getNodes() as $node) {
            $output .= $this->outputNode($node, $indent);
        }

        return $output;
    }
}
