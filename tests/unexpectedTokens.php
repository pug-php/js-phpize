<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class UnexpectedTokensTest extends TestCase
{
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
        $jsPhpize = new JsPhpize(array(
            'strict' => true,
        ));
        $jsPhpize->render('a = true; )');
    }
}
