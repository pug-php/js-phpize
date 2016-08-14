<?php

use JsPhpize\JsPhpize;

class ExamplesTest extends \PHPUnit_Framework_TestCase
{
    public function caseProvider()
    {
        $cases = array();

        $examples = __DIR__ . '/../examples';
        foreach (scandir($examples) as $file) {
            if (substr($file, -3) === '.js') {
                $cases[] = array($examples . '/' . substr($file, 0, -3) . '.return', $examples . '/' . $file);
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

        $this->assertSame($expected, $actual, $jsFile . ' should return ' . $expected);
    }
}
