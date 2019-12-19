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
            var_dump($rest);
            exit;
            $rest = substr($rest, strlen($match[0]));
        }

        return parent::lexWith($lexer);
    }
}
