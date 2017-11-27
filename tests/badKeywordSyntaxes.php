<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class BadKeywordSyntaxesTest extends TestCase
{
    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 17
     */
    public function testIfWithoutParentheses()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('if "a" {
            return 6;
        }');
    }
}
