<?php

use JsPhpize\JsPhpize;

class BadSwitchSyntaxesTest extends \PHPUnit_Framework_TestCase
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
