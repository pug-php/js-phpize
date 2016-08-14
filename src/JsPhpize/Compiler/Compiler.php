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
            return $indent . $block->getHead() . '{' . "\n" .
                $this->compile($node, '  ' . $indent) .
                '}';
        }
        if ($node instanceof Comment) {
            return $indent . $node;
        }

        return $indent . rtrim($node, ';') . ';';
    }

    public function compile(Block $block, $indent = '')
    {
        $output = '';

        foreach ($block->getNodes() as $node) {
            $output .= $this->outputNode($node, $indent) . "\n";
        }

        return $output;
    }
}
