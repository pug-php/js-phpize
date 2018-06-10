<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class ArrayTest extends TestCase
{
    public function caseProvider()
    {
        return array(
            array(
                1,
                'var a = []; return a.push(4);',
            ),
            array(
                2,
                'var a = []; a.push(4); return a.push(4);',
            ),
            array(
                array(4),
                'var a = []; a.push(4); return a;',
            ),
        );
    }

    /**
     * @group array
     * @dataProvider caseProvider
     */
    public function testArrayResult($expected, $code)
    {
        $jsPhpize = new JsPhpize(array(
            'helpers' => array(
                'dot' => 'dotWithArrayPrototype',
            ),
        ));
        $actual = $jsPhpize->render($code);

        $this->assertSame($expected, $actual);
    }
}
