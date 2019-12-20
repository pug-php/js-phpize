<?php

namespace JsPhpize\Lexer\Patterns;

use Generator;
use JsPhpize\Lexer\Lexer;
use JsPhpize\Lexer\Pattern;

class StringPattern extends Pattern
{
    public function __construct($priority)
    {
        parent::__construct($priority, 'string', '"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'|`(?:\\\\.|[^`\\\\])*`');
    }

    public function lexWith(Lexer $lexer): Generator
    {
        $rest = $lexer->rest();

        if (preg_match('/^\s*`/', $rest, $match)) {
        }

        return parent::lexWith($lexer);
    }
}
