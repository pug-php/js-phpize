<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class UnexpectedTokensInBracketsTest extends TestCase
{
    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testDoubleCommaInBrackets()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{5,,8}');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testMissingCommaInBrackets()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a:8 if}');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testKeywordInsteadOfValueInBrackets()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a:if}');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testKeywordInBrackets()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{5 if}');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testKeywordAfterValueInBrackets()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a:5, if}');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testOperatorAfterValueInBrackets()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a:5, +}');
    }
}
