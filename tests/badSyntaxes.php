<?php

use JsPhpize\JsPhpize;
use JsPhpize\Nodes\FunctionCall;
use JsPhpize\Nodes\Instruction;
use PHPUnit\Framework\TestCase;

class BadSyntaxesTest extends TestCase
{
    public function testNoParenthesesClose()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Missing ) to match  on line 1 near from if (',
            5
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('if ( {}');
    }

    public function testNoHookClose()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Missing ] to match  on line 1 near from a = [',
            6
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = [1,');
    }

    public function testBracketMissingAfterKey()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            "Missing value after 'a' on line 1 near from {a",
            12
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('{a');
    }

    public function testNoBracketClose()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Missing } to match  on line 1 near from a = {',
            7
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = {a: "b",');
    }

    public function testValueExpected()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Value expected after  on line 1 near from a =',
            20
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a =');
    }

    public function testBadHookUsage()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Missing ] to match  on line 1 near from a[',
            13
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a[4');
    }

    public function testTernaryMissClosedAfterQuestionMark()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            "Ternary expression not properly closed after '?'  on line 1 near from true",
            14
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = true ? true');
    }

    public function testTernaryExpectColon()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            "':' expected but if given  on line 1 near from if",
            15
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = true ? (true) if');
    }

    public function testTernaryMissingFalseValue()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            "Ternary expression not properly closed after ':'  on line 1 near from true :",
            16
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a = true ? true :');
    }

    public function testBadFunctionCall()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected called type JsPhpize\Nodes\Instruction',
            24
        ));

        new FunctionCall(new Instruction(), [], []);
    }

    public function testNewWithNothingCall()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            "Class name expected after 'new'",
            25
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->compile('new');
    }

    public function testCloneWithNothingCall()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            "Object expected after 'clone'",
            25
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->compile('clone');
    }

    public function testPhpCode()
    {
        self::expectExceptionObject(new \JsPhpize\Lexer\Exception(
            'Unexpected token :: on line 1 near from ::',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('DateTime::createFromFormat(\'j-M-Y\', \'15-Feb-2009\')');
    }

    public function testUnterminatedString()
    {
        self::expectExceptionObject(new \JsPhpize\Lexer\Exception(
            'Unterminated ` string after `foo ${`bar`}',
            27
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('`foo ${`bar`}');
    }
}
