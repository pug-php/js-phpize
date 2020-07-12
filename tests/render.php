<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class RenderTest extends TestCase
{
    public function caseProvider()
    {
        $cases = [];

        $examples = __DIR__ . '/../examples';

        foreach (scandir($examples) as $file) {
            if (substr($file, -7) === '.return') {
                $cases[] = [$file, substr($file, 0, -7) . '.js'];
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
        $jsPhpize = new JsPhpize([
            'helpers' => [
                'dot' => 'dotWithArrayPrototype',
            ],
        ]);
        $expected = file_get_contents($examples . '/' . $returnFile);

        try {
            $result = $jsPhpize->render($examples . '/' . $jsFile);
        } catch (Throwable $error) {
            $contents = $jsPhpize->compile($examples . '/' . $jsFile);
            $message = "\n" . get_class($error) . ' in ' . $jsFile . ' line ' . $error->getLine() .
                "\n" . $error->getMessage() . "\n";

            foreach (explode("\n", $contents) as $index => $line) {
                $number = $index + 1;
                $message .= ($number === $error->getLine() ? '>' : ' ') .
                    str_repeat(' ', 4 - strlen($number)) .
                    $number . ' | ' .
                    $line . "\n";
            }

            throw new Exception($message, 1, $error);
        }

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

    public function testPropStringCast()
    {
        $data = [
            'obj' => new class() {
                public function __call($name, $args)
                {
                    return 'foo';
                }

                public function __get($name)
                {
                    if ($name === 'objectData') {
                        return (object) ['foo' => 'o-bar'];
                    }

                    if ($name === 'arrayData') {
                        return ['foo' => 'a-bar'];
                    }

                    return $name === 'prop' ? null : 'else';
                }
            },
        ];

        $jsPhpize = new JsPhpize();

        $result = $jsPhpize->renderCode('return obj.prop', $data);
        $this->assertSame('', (string) $result);
        $this->assertFalse($result->__toBoolean());

        $result = $jsPhpize->renderCode('return obj.other', $data);
        $this->assertSame('else', (string) $result);
        $this->assertTrue($result->__toBoolean());

        $result = $jsPhpize->renderCode('return obj.objectData.foo', $data);
        $this->assertSame('o-bar', (string) $result);
        $this->assertTrue($result->__toBoolean());

        $result = $jsPhpize->renderCode('return obj.arrayData.foo', $data);
        $this->assertSame('a-bar', (string) $result);
        $this->assertTrue($result->__toBoolean());
    }

    public function testDollarVariablePrefix()
    {
        $jsPhpize = new JsPhpize([
            'ignoreDollarVariable' => true,
        ]);
        $code = 'return isset($variable) && $variable !== false';

        $this->assertFalse($jsPhpize->renderCode($code));
        $this->assertTrue($jsPhpize->renderCode($code, ['variable' => 1]));
        $this->assertFalse($jsPhpize->renderCode($code, ['variable' => false]));

        $code = 'return $foo && isset($bar[$foo])';

        $this->assertFalse($jsPhpize->renderCode($code, ['foo' => 'a']));
        $this->assertTrue($jsPhpize->renderCode($code, ['foo' => 'a', 'bar' => ['a' => 'x']]));

        $code = 'return $foo && isset($bar.foo)';

        $this->assertFalse($jsPhpize->renderCode($code, ['foo' => 'a']));
        $this->assertTrue($jsPhpize->renderCode($code, ['foo' => 'a', 'bar' => (object) ['foo' => 'x']]));

        $code = 'return $foo && isset($bar["foo"])';

        $this->assertFalse($jsPhpize->renderCode($code, ['foo' => 'a']));
        $this->assertTrue($jsPhpize->renderCode($code, ['foo' => 'a', 'bar' => ['foo' => 'x']]));
    }

    public function testClassDeclarationNesting()
    {
        $jsPhpize = new JsPhpize();
        $code = 'class Main { public function run() {' .
            '$foo = ["bar" => "hello"];' .
            $jsPhpize->compile('return foo.bar') .
            '}} $main = new Main; echo $main->run();';

        ob_start();
        eval($code);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame('hello', $output);
    }
}
