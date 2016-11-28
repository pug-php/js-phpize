<?php

use JsPhpize\JsPhpize;

class UnexpectedTokensTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testConsecutivesNumbersInParentheses()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('(5 6)');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testHooksAfterNumber()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('(5 [8])');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testBadParenthesesClose()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('(}');
    }

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
    public function testBadDotUsage()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a.()');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testBadHookClose()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a[4)');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testLonelyIncrement()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('++');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testFunctionWithoutParentheses()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('myFunction = function {}');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testFunctionWithoutBody()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('myFunction = function ();');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testBracketAfterVariable()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('myVar{}');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testExpecteNoLeftMember()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('myVar!');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testBadLet()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('let false');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testUnexpectedInBlock()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = true; )');
    }
}
