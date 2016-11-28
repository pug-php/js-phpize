<?php

use JsPhpize\JsPhpize;

class UnexpectedTokensInParenthesesTest extends \PHPUnit_Framework_TestCase
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

}
