<?php

use JsPhpize\JsPhpize;

class BadSyntaxesTest extends \PHPUnit_Framework_TestCase
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
     * @expectedExceptionCode 5
     */
    public function testNoParenthesesClose()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('(');
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
     * @expectedExceptionCode 6
     */
    public function testNoHookClose()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('[');
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
     * @expectedExceptionCode 12
     */
    public function testBracketMissingAfterKey()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a');
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
     * @expectedExceptionCode 7
     */
    public function testNoBracketClose()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{');
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
     * @expectedExceptionCode 20
     */
    public function testValueExpected()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a[');
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

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 8
     */
    public function testUnexpectedInBlock()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = true; )');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 21
     */
    public function testCaseWithoutColon()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('
            switch (foo) {
                case 4;
                    foo++;
                    break;
            }
        ');
    }

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 22
     */
    public function testDefaultWithoutColon()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('
            switch (foo) {
                default;
                    foo++;
            }
        ');
    }
}
