<?php

use JsPhpize\JsPhpize;
use JsPhpize\Nodes\Constant;

class ConstantRestrictionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException     \JsPhpize\Parser\Exception
     * @expectedExceptionCode 23
     */
    public function testBadConstantType()
    {
        new Constant('operator', '+');
    }

    /**
     * @expectedException              \JsPhpize\Parser\Exception
     * @expectedExceptionCode          9
     * @expectedExceptionMessageRegExp /string is not assignable/
     */
    public function testAssignationToString()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('"foo" = "bar"');
    }

    /**
     * @expectedException              \JsPhpize\Parser\Exception
     * @expectedExceptionCode          9
     * @expectedExceptionMessageRegExp /NAN is not assignable/
     */
    public function testAssignationToNaN()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('NaN = "bar"');
    }

    /**
     * @expectedException              \JsPhpize\Parser\Exception
     * @expectedExceptionCode          9
     * @expectedExceptionMessageRegExp /'M_' prefix is reserved to mathematical constants/
     */
    public function testAssignationToMathConstant()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('M_FOO = "bar"');
    }
}
