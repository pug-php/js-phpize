<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class BadSwitchSyntaxesTest extends TestCase
{
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
