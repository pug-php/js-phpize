<?php

use JsPhpize\JsPhpize;

class BadSyntaxesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 5
     */
    public function testNoParenthesesClose()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('if ( {}');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 6
     */
    public function testNoHookClose()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = [1,');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 12
     */
    public function testBracketMissingAfterKey()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 7
     */
    public function testNoBracketClose()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = {a: "b",');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 20
     */
    public function testValueExpected()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a =');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 13
     */
    public function testBadHookUsage()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a[4');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 14
     */
    public function testTernaryMissClosedAfterQuestionMark()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = true ? true');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 15
     */
    public function testTernaryExpectColon()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = true ? (true) if');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 16
     */
    public function testTernaryMissingFalseValue()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = true ? true :');
    }
}
