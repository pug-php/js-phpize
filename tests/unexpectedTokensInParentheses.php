<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class UnexpectedTokensInParenthesesTest extends TestCase
{
    public function testConsecutiveNumbersInParentheses()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected number 6 on line 1 near from (5 6',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('(5 6)');
    }

    public function testHooksAfterNumber()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected [ on line 1 near from (5 [',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('(5 [8])');
    }

    public function testBadParenthesesClose()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'Unexpected } on line 1 near from (}',
            8
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('(}');
    }
}
