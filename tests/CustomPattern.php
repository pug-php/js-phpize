<?php

class CustomPattern extends \JsPhpize\Lexer\Pattern
{
    public function __construct()
    {
        parent::__construct(42, 'string', []);
    }

    public function lexWith(\JsPhpize\Lexer\Lexer $lexer): Generator
    {
        if (substr($lexer->rest(), 0, 1) === '@') {
            $lexer->consume(1);
            yield new \JsPhpize\Lexer\Token('string', ['value' => '"@1"']);
            yield new \JsPhpize\Lexer\Token(',', []);
            yield new \JsPhpize\Lexer\Token('string', ['value' => '"@2"']);
        }
    }
}
