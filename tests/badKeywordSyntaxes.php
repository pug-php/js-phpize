<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class BadKeywordSyntaxesTest extends TestCase
{
    public function testIfWithoutParentheses()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            "'if' block need parentheses.",
            17
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('if "a" {
            return 6;
        }');
    }
}
