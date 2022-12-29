<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class UnexpectedTokensInHooksTest extends TestCase
{
    public function testConsecutiveNumbersInHooks()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected number 6 on line 1 near from [5 6',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('[5 6]');
    }

    public function testDoubleCommaInHooks()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected , on line 1 near from [5,,',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('[5,,8]');
    }

    public function testKeywordInHooks()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected keyword if on line 1 near from if',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('[5 if]');
    }
}
