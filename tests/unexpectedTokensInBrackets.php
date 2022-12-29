<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class UnexpectedTokensInBracketsTest extends TestCase
{
    public function testDoubleCommaInBrackets()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected , on line 1 near from {5,',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{5,,8}');
    }

    public function testMissingCommaInBrackets()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected keyword if on line 1 near from if',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a:8 if}');
    }

    public function testKeywordInsteadOfValueInBrackets()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected keyword if on line 1 near from if',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a:if}');
    }

    public function testKeywordInBrackets()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected keyword if on line 1 near from if',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{5 if}');
    }

    public function testKeywordAfterValueInBrackets()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected } on line 1 near from if}',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a:5, if}');
    }

    public function testOperatorAfterValueInBrackets()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected + on line 1 near from {a:5, +',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a:5, +}');
    }
}
