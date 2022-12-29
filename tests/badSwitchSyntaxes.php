<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class BadSwitchSyntaxesTest extends TestCase
{
    public function testCaseWithoutColon()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            "'case' must be followed by a value and a colon.",
            21
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('
            switch (foo) {
                case 4;
                    foo++;
                    break;
            }
        ');
    }

    public function testDefaultWithoutColon()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            "'default' must be followed by a colon.",
            22
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('
            switch (foo) {
                default;
                    foo++;
            }
        ');
    }
}
