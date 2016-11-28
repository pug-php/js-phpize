<?php

use JsPhpize\JsPhpize;

class UnexpectedTokensInHooksTest extends \PHPUnit_Framework_TestCase
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
