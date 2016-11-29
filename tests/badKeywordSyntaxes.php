<?php

use JsPhpize\JsPhpize;

class BadKeywordSyntaxesTest extends \PHPUnit_Framework_TestCase
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
