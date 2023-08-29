<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class MainMethodsTest extends TestCase
{
    public function testCompileFile()
    {
        $jsPhpize = new JsPhpize();
        $actual = $jsPhpize->compileFile(__DIR__ . '/../examples/basic.js');
        $expected = '$GLOBALS[\'__jpv_dot\'] = ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Dot.h') . ';';
        $expected .= '$GLOBALS[\'__jpv_dot_with_ref\'] = ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Dot.ref.h') . ';';
        $expected .= '$GLOBALS[\'__jpv_plus\'] = ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Plus.h') . ';';
        $expected .= '$GLOBALS[\'__jpv_plus_with_ref\'] = $GLOBALS[\'__jpv_plus\'];';
        $expected .= <<<'EOD'
$foo = array( 'bar' => array( "baz" => "hello" ) );
$biz = 'bar';
return $GLOBALS['__jpv_plus']($GLOBALS['__jpv_dot_with_ref']($foo, 'bar', "baz"), ' ', $GLOBALS['__jpv_dot_with_ref']($foo, $biz, 'baz'), " ", $GLOBALS['__jpv_dot_with_ref']($foo, 'bar', 'baz'));
EOD;
        $actual = str_replace(';', ";\n", preg_replace('/\s/', '', $actual));
        $expected = str_replace(';', ";\n", preg_replace('/\s/', '', $expected));
        $this->assertSame($expected, $actual);
        $this->assertSame('', $jsPhpize->compileDependencies());

        $jsPhpizeCatchDeps = new JsPhpize([
            'catchDependencies' => true,
        ]);
        $actual = $jsPhpizeCatchDeps->compileFile(__DIR__ . '/../examples/basic.js');
        $expected = <<<'EOD'
$foo = array( 'bar' => array( "baz" => "hello" ) );
$biz = 'bar';
return $GLOBALS['__jpv_plus']($GLOBALS['__jpv_dot_with_ref']($foo, 'bar', "baz"), ' ', $GLOBALS['__jpv_dot_with_ref']($foo, $biz, 'baz'), " ", $GLOBALS['__jpv_dot_with_ref']($foo, 'bar', 'baz'));
EOD;
        $actual = preg_replace('/\s/', '', $actual);
        $expected = preg_replace('/\s/', '', $expected);
        $this->assertSame($expected, $actual);

        $actual = $jsPhpizeCatchDeps->compileDependencies();
        $jsPhpizeCatchDeps->flushDependencies();
        $expected = '$GLOBALS[\'__jpv_dot\'] = ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Dot.h') . ';';
        $expected .= '$GLOBALS[\'__jpv_dot_with_ref\'] = ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Dot.ref.h') . ';';
        $expected .= '$GLOBALS[\'__jpv_plus\'] = ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Plus.h') . ';';
        $expected .= '$GLOBALS[\'__jpv_plus_with_ref\'] = $GLOBALS[\'__jpv_plus\'];';

        $actual = preg_replace('/\s/', '', $actual);
        $expected = preg_replace('/\s/', '', $expected);
        $this->assertSame($expected, $actual);

        $jsPhpizeCatchDeps->compileFile(__DIR__ . '/../examples/calcul.js');
        $actual = $jsPhpizeCatchDeps->compileDependencies();
        $expected = '$GLOBALS[\'__jpv_plus\'] = ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Plus.h') . ';';
        $expected .= '$GLOBALS[\'__jpv_plus_with_ref\'] = $GLOBALS[\'__jpv_plus\'];';
        $actual = preg_replace('/\s/', '', $actual);
        $expected = preg_replace('/\s/', '', $expected);
        $this->assertSame($expected, $actual);
    }

    public function testCompileFileMissing()
    {
        self::expectExceptionObject(new \Exception(
            'No such file',
            1
        ));

        try {
            $jsPhpize = new JsPhpize();
            $jsPhpize->compileFile('does/not/exists.js');
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage(), 1);
        }
    }

    public function testCompileConcat()
    {
        $jsPhpize = new JsPhpize();
        $actual = $jsPhpize->render('return "group[" + group.id + "]"', [
            'group' => (object) [
                'id' => 4,
            ],
        ]);
        $expected = 'group[4]';

        $this->assertSame($expected, $actual);
    }

    /**
     * @group concat
     */
    public function testConcatenation()
    {
        $jsPhpize = new JsPhpize();
        $actual = $jsPhpize->render("return 'a' + a.i", [
            'a' => [
                'i' => 'b',
            ],
        ]);
        $expected = 'ab';

        $this->assertSame($expected, $actual);
    }

    public function testCompileSource()
    {
        $jsPhpize = new JsPhpize([
            'varPrefix' => 'foo',
        ]);
        $actual = $jsPhpize->compileCode('b = 8');
        $expected = '$b = 8;';
        $actual = preg_replace('/\s/', '', $actual);
        $expected = preg_replace('/\s/', '', $expected);
        $this->assertSame($expected, $actual);

        $dir = getcwd();
        chdir(__DIR__ . '/../examples');
        $actual = $jsPhpize->compileCode('calcul.js');
        chdir($dir);
        $expected = '$GLOBALS[\'foodot\'] = ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Dot.h') . ';';
        $expected .= '$GLOBALS[\'foodot_with_ref\'] = ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Dot.ref.h') . ';';

        $expected .= <<<'EOD'
$GLOBALS['foodot_with_ref']($calcul, 'js');
EOD;
        $actual = preg_replace('/\s/', '', $actual);
        $expected = preg_replace('/\s/', '', $expected);
        $this->assertSame($expected, $actual);
    }

    public function testRender()
    {
        $jsPhpize = new JsPhpize();
        $actual = $jsPhpize->render('return b;', [
            'b' => 42,
        ]);
        $expected = 42;
        $this->assertSame($expected, $actual);

        error_reporting(E_ALL ^ (PHP_VERSION < 8 ? E_NOTICE : E_WARNING));
        $actual = $jsPhpize->render('return b;');
        $expected = null;
        $this->assertSame($expected, $actual);

        $jsPhpize->share('b', [31]);
        $actual = $jsPhpize->render('return b;');
        $expected = [31];
        $this->assertSame($expected, $actual);

        $jsPhpize->resetSharedVariables();
        $actual = $jsPhpize->render('return b;');
        $expected = null;
        $this->assertSame($expected, $actual);
        error_reporting(-1);
    }
}
