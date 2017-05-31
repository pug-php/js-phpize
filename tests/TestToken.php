<?php

use JsPhpize\Lexer\Token;

class TestToken extends Token
{
    protected function isArithmetic()
    {
        if ($this->is('@')) {
            return true;
        }

        return parent::isArithmetic();
    }
}
