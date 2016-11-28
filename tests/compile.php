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
                $cases[] = array($file, substr($file, 0, -4) . '.js');
            }
        }

        return $cases;
    }

    /**
     * @dataProvider caseProvider
     */
    public function testJsPhpizeGeneration($phpFile, $jsFile)
    {
        $examples = __DIR__ . '/../examples';
        $jsPhpize = new JsPhpize();
        $expected = file_get_contents($examples . '/' . $phpFile);
        $result = $jsPhpize->compile($examples . '/' . $jsFile);

        $expected = str_replace("\r", '', trim($expected));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual, $jsFile . ' should compile into ' . $expected);
    }

    public function testCompileWithoutDependencies()
    {
        $jsPhpize = new JsPhpize();
        $result = $jsPhpize->compileWithoutDependencies('4 + 5');

        $expected = str_replace("\r", '', trim("call_user_func(\$GLOBALS['__jpv_plus'], 4, 5);"));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException     \JsPhpize\Compiler\Exception
     * @expectedExceptionCode 1111111
     */
    public function testCompilerException()
    {
        $jsPhpize = new JsPhpize();
        $result = $jsPhpize->render('a()', array(
            'a' => function () {
                throw new \JsPhpize\Compiler\Exception('custom', 1111111);
            },
        ));
    }

    /**
     * @expectedException           \JsPhpize\Compiler\Exception
     * @expectedExceptionCode       2
     * @expectedExceptionCodeRegExp /An error occur in \[foo = 9/
     */
    public function testCompilerWrappedException()
    {
        $jsPhpize = new JsPhpize();
        $result = $jsPhpize->render('
            foo = 9;
            /* here come a very long long long long long long long long long
             * long long long long long long long long long long long long long
             * long long long long long long long long long long long long long
             * long long long long long long long long long long long long long
             * comment
             */
            a();
        ', array(
            'a' => function () {
                throw new \Exception('custom', 123456);
            },
        ));
    }
}
