<?php

use JsPhpize\JsPhpize;

class CompileTest extends \PHPUnit_Framework_TestCase
{
    public function caseProvider()
    {
        $cases = array();

        $examples = __DIR__ . '/../examples';
        foreach (scandir($examples) as $file) {
            if (substr($file, -4) === '.php') {
                $cases[] = array($examples . '/' . $file, $examples . '/' . substr($file, 0, -4) . '.js');
            }
        }

        return $cases;
    }

    /**
     * @dataProvider caseProvider
     */
    public function testJsPhpizeGeneration($phpFile, $jsFile)
    {
        $jsPhpize = new JsPhpize();
        $expected = file_get_contents($phpFile);
        $result = $jsPhpize->compile($jsFile);

        $expected = str_replace("\r", '', trim($expected));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual, $jsFile . ' should compile into ' . $expected);
    }
}
