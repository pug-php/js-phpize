<?php

use JsPhpize\JsPhpize;

class RenderTest extends \PHPUnit_Framework_TestCase
{
    public function caseProvider()
    {
        $cases = array();

        $examples = __DIR__ . '/../examples';
        foreach (scandir($examples) as $file) {
            if (substr($file, -7) === '.return' && $file === 'prototype.return') {
                $cases[] = array($file, substr($file, 0, -7) . '.js');
            }
        }

        return $cases;
    }

    /**
     * @group examples
     * @dataProvider caseProvider
     */
    public function testJsPhpizeGeneration($returnFile, $jsFile)
    {
        $examples = __DIR__ . '/../examples';
        $jsPhpize = new JsPhpize();
        $expected = file_get_contents($examples . '/' . $returnFile);
        $result = $jsPhpize->render($examples . '/' . $jsFile);

        $expected = str_replace("\r", '', trim($expected));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual, $jsFile . ' should render ' . $expected);
    }

    public function testRenderFile()
    {
        $examples = __DIR__ . '/../examples';
        $jsPhpize = new JsPhpize();
        $expected = file_get_contents($examples . '/basic.return');
        $result = $jsPhpize->renderFile($examples . '/basic.js');

        $expected = str_replace("\r", '', trim($expected));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual);
    }

    public function testRenderCode()
    {
        $examples = __DIR__ . '/../examples';
        $jsPhpize = new JsPhpize();
        $expected = file_get_contents($examples . '/basic.return');
        $result = $jsPhpize->renderCode(file_get_contents($examples . '/basic.js'));

        $expected = str_replace("\r", '', trim($expected));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual);
    }
}
