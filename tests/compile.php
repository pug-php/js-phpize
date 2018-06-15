<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class CompileTest extends TestCase
{
    public function caseProvider()
    {
        $cases = [];

        $examples = __DIR__ . '/../examples';
        foreach (scandir($examples) as $file) {
            if (substr($file, -4) === '.php') {
                $cases[] = [$file, substr($file, 0, -4) . '.js'];
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
        $jsPhpize = new JsPhpize([
            'catchDependencies' => true,
        ]);
        $result = $jsPhpize->compileCode('4 + 5');

        $expected = str_replace("\r", '', trim("\$GLOBALS['__jpv_plus'](4, 5);"));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual);
    }

    public function testDependenciesGrouping()
    {
        $jsPhpize = new JsPhpize([
            'catchDependencies' => true,
        ]);
        $jsPhpize->compileCode("a = 4 + 5;\n b = '9' + '3'");
        $jsPhpize->compileCode('4 + 5');
        $jsPhpize->compileCode('4 + 5 + 9');
        $jsPhpize->compileCode('a.b.c');
        $jsPhpize->compileCode("a.b();\nc = a.c");
        $jsPhpize->compileCode('a.b');
        $jsPhpize->compileCode('a.b');

        $this->assertSame(1, mb_substr_count(
            $jsPhpize->compileDependencies(),
            '$GLOBALS[\'__jpv_dot\'] = function ($base) {'
        ));

        $this->assertSame(1, mb_substr_count(
            $jsPhpize->compileDependencies(),
            '$GLOBALS[\'__jpv_plus\'] = function ($base) {'
        ));
    }

    public function testTruncatedCode()
    {
        $jsPhpize = new JsPhpize([
            'catchDependencies' => true,
        ]);
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
        $jsPhpize = new JsPhpize([
            'ignoreDollarVariable' => true,
        ]);

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
        $jsPhpize->render('a()', [
            'a' => function () {
                throw new \JsPhpize\Compiler\Exception('custom', 1111111);
            },
        ]);
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
        ', [
            'a' => function () {
                throw new \Exception('custom', 123456);
            },
        ]);
    }

    public function testJsonInvalidMethod()
    {
        $jsPhpize = new JsPhpize();
        $code = $jsPhpize->compile('JSON.doesNotExists()');

        $this->assertSame(
            "JSON;\n(function_exists('doesNotExists') ? doesNotExists() : \$doesNotExists());",
            trim($code)
        );
    }
}
