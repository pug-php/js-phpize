<?php

use JsPhpize\JsPhpize;

class RenderTest extends \PHPUnit_Framework_TestCase
{
    public function caseProvider()
    {
        $cases = array();

        $examples = __DIR__ . '/../examples';
        foreach (scandir($examples) as $file) {
            if (substr($file, -7) === '.return') {
                $cases[] = array($examples . '/' . $file, $examples . '/' . substr($file, 0, -7) . '.js');
            }
        }

        return $cases;
    }

    /**
     * @dataProvider caseProvider
     */
    public function testJsPhpizeGeneration($returnFile, $jsFile)
    {
        $jsPhpize = new JsPhpize();
        $expected = file_get_contents($returnFile);
        $result = $jsPhpize->render($jsFile);

        $expected = trim($expected);
        $actual = trim($result);

        $this->assertSame($expected, $actual, $jsFile . ' should render ' . $expected);
    }
}