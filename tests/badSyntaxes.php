<?php

use JsPhpize\JsPhpize;
use JsPhpize\Nodes\FunctionCall;
use JsPhpize\Nodes\Instruction;
use PHPUnit\Framework\TestCase;

class BadSyntaxesTest extends TestCase
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

    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 24
     */
    public function testBadFunctionCall()
    {
        new FunctionCall(new Instruction(), [], []);
    }

    /**
     * @expectedException        \JsPhpize\Parser\Exception
     * @expectedExceptionCode    25
     * @expectedExceptionMessage Class name expected after 'new'
     */
    public function testNewWithNothingCall()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->compile('new');
    }

    /**
     * @expectedException        \JsPhpize\Parser\Exception
     * @expectedExceptionCode    25
     * @expectedExceptionMessage Object expected after 'clone'
     */
    public function testCloneWithNothingCall()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->compile('clone');
    }

    /**
     * @expectedException     \JsPhpize\Lexer\Exception
     * @expectedExceptionCode 8
     */
    public function testPhpCode()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('DateTime::createFromFormat(\'j-M-Y\', \'15-Feb-2009\')');
    }
}
