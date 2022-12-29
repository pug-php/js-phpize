<?php

use JsPhpize\JsPhpize;
use JsPhpize\Nodes\Constant;
use PHPUnit\Framework\TestCase;

class ConstantRestrictionsTest extends TestCase
{
    public function testBadConstantType()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'The given type [operator] is not a valid constant type.',
            23
        ));

        new Constant('operator', '+');
    }

    public function testAssignationToString()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'string is not assignable',
            9
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('"foo" = "bar"');
    }

    public function testAssignationToNaN()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            'NAN is not assignable',
            9
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('NaN = "bar"');
    }

    public function testAssignationToMathConstant()
    {
        self::expectExceptionObject(new \JsPhpize\Parser\Exception(
            "'M_' prefix is reserved to mathematical constants",
            9
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('M_FOO = "bar"');
    }
}
