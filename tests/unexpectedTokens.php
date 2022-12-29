<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class UnexpectedTokensTest extends TestCase
{
    public function testBadDotUsage()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected ( on line 1 near from a.(',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a.()');
    }

    public function testBadHookClose()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected ) on line 1 near from a[4)',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a[4)');
    }

    public function testLonelyIncrement()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected ++ on line 1 near from ++',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('++');
    }

    public function testFunctionWithoutParentheses()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected { on line 1 near from function {',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('myFunction = function {}');
    }

    public function testFunctionWithoutBody()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected ; on line 1 near from function ();',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('myFunction = function ();');
    }

    public function testBracketAfterVariable()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected { on line 1 near from myVar{',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('myVar{}');
    }

    public function testExpectNoLeftMember()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected ! on line 1 near from myVar!',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('myVar!');
    }

    public function testBadLet()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected constant false on line 1 near from false',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('let false');
    }

    public function testUnexpectedInBlock()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected ) on line 1 near from true; )',
            8
        ));

        $jsPhpize = new JsPhpize([
            'strict' => true,
        ]);
        $jsPhpize->render('a = true; )');
    }
}
