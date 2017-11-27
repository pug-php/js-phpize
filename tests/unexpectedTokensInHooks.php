<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class UnexpectedTokensInHooksTest extends TestCase
{
    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testConsecutivesNumbersInHooks()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('[5 6]');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testDoubleCommaInHooks()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('[5,,8]');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testKeywordInHooks()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('[5 if]');
    }
}
