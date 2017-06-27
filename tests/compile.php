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
     * @group examples
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
        $jsPhpize = new JsPhpize(array(
            'catchDependencies' => true,
        ));
        $result = $jsPhpize->compileCode('4 + 5');

        $expected = str_replace("\r", '', trim("call_user_func(\$GLOBALS['__jpv_plus'], 4, 5);"));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual);
    }

    public function testTruncatedCode()
    {
        $jsPhpize = new JsPhpize(array(
            'catchDependencies' => true,
        ));
        $result = $jsPhpize->compileCode('} else {');

        $expected = str_replace("\r", '', trim('} else {'));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual);

        $result = $jsPhpize->compileCode('}');

        $expected = str_replace("\r", '', trim('}'));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual);
    }

    public function testCompileDollar()
    {
        $jsPhpize = new JsPhpize(array(
            'ignoreDollarVariable' => true,
        ));

        $actual = trim($jsPhpize->compile('$item'));

        $this->assertSame('$item;', $actual);

        $actual = trim($jsPhpize->compile('isset(item) ? item : \'\''));

        $this->assertSame('isset($item) ? $item : \'\';', $actual);
    }

    public function testDoubleParentheses()
    {
        $jsPhpize = new JsPhpize();
        $template = "(('is regular, javascript'))";

        $actual = trim($jsPhpize->compile($template));

        $this->assertSame($template . ';', $actual);
    }

    /**
     * @expectedException     \JsPhpize\Compiler\Exception
     * @expectedExceptionCode 1111111
     */
    public function testCompilerException()
    {
        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a()', array(
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
        $jsPhpize->render('
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
