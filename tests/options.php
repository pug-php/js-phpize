<?php

use JsPhpize\JsPhpize;
use JsPhpize\Lexer\Patterns;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    /**
     * @group disallow
     */
    public function testDisallow()
    {
        $jsPhpize = new JsPhpize([
            'disallow' => 'foo bar',
        ]);
        $this->assertSame(6, $jsPhpize->render('
            var a = 3;
            for (i = 0; i < 3; i++) {
                a++; // increment
            }
            return a;
        '));

        $jsPhpize = new JsPhpize([
            'disallow' => 'foo comment bar',
        ]);
        $code = null;

        try {
            $jsPhpize->render('
                var a = 3;
                for (i = 0; i < 3; i++) {
                    a++; // increment
                }
                return a;
            ');
        } catch (\Exception $exception) {
            $code = $exception->getCode();
        }

        $this->assertSame(3, $code);
    }

    /**
     * @expectedException     \JsPhpize\Lexer\Exception
     * @expectedExceptionCode 1
     */
    public function testConstPrefixRestriction()
    {
        $jsPhpize = new JsPhpize([
            'constPrefix' => 'FOO',
        ]);
        $jsPhpize->render('
            var a = FOOBAR;
        ');
    }

    /**
     * @expectedException     \JsPhpize\Lexer\Exception
     * @expectedExceptionCode 4
     */
    public function testVarPrefixRestriction()
    {
        $jsPhpize = new JsPhpize([
            'varPrefix' => 'test',
        ]);
        $jsPhpize->render('
            var a = test_zz;
        ');
    }

    /**
     * @group return
     */
    public function testReturnLastStatement()
    {
        $jsPhpize = new JsPhpize([
            'returnLastStatement' => true,
        ]);
        $eleven = $jsPhpize->render('
            var a = 8;
            a + 3;
        ');
        $this->assertSame(11, $eleven);

        $jsPhpize = new JsPhpize([
            'returnLastStatement' => false,
        ]);
        $defaultReturn = $jsPhpize->render('
            var a = 8;
            a + 3;
        ');
        $this->assertSame(1, $defaultReturn);
    }

    /**
     * @group keyword
     */
    public function testKeyword()
    {
        $jsPhpize = new JsPhpize();
        $eleven = $jsPhpize->render('
            var deleteThing = 11;
            return deleteThing;
        ');
        $this->assertSame(11, $eleven);
    }

    /**
     * @group patterns
     */
    public function testPatterns()
    {
        include_once __DIR__ . '/TestToken.php';
        $jsPhpize = new JsPhpize([
            'tokenClass' => 'TestToken',
        ]);
        $jsPhpize->addPattern(new Pattern(0, 'operator', '@'));
        $code = $jsPhpize->compile('1 @ 8');
        $this->assertSame('1 @ 8;', trim($code));
    }

    /**
     * @group patterns
     * @expectedException     \JsPhpize\Lexer\Exception
     * @expectedExceptionCode 12
     */
    public function testPatternsException()
    {
        include_once __DIR__ . '/TestToken.php';
        $jsPhpize = new JsPhpize([
            'tokenClass' => 'TestToken',
        ]);
        $jsPhpize->removePatterns(function (Pattern $pattern) {
            return !in_array($pattern->type, ['number', 'operator']);
        });
        $jsPhpize->compile('1 + 1');
    }

    public function testDisableConstants()
    {
        $jsPhpize = new JsPhpize();
        self::assertSame('FOO', trim($jsPhpize->compile('FOO'), " \n;"));
        $jsPhpize = new JsPhpize([
            'disableConstants' => true,
        ]);
        self::assertSame('$FOO', trim($jsPhpize->compile('FOO'), " \n;"));
    }

    /**
     * @throws \JsPhpize\Compiler\Exception
     * @throws \JsPhpize\Lexer\Exception
     * @throws \JsPhpize\Parser\Exception
     */
    public function testBooleanLogicalOperators()
    {
        $jsPhpize = new JsPhpize();
        $this->assertSame(4, $jsPhpize->render('return 7 && 4'));
        $this->assertSame(7, $jsPhpize->render('return 7 || 4'));
        $this->assertSame(null, $jsPhpize->render('return null && false'));
        $this->assertSame(null, $jsPhpize->render('return 0 || null'));

        $jsPhpize = new JsPhpize([
            'booleanLogicalOperators' => true,
        ]);
        $this->assertSame(true, $jsPhpize->render('return 7 && 4'));
        $this->assertSame(true, $jsPhpize->render('return 7 || 4'));
        $this->assertSame(false, $jsPhpize->render('return null && false'));
        $this->assertSame(false, $jsPhpize->render('return 0 || null'));
    }

    /**
     * @throws \JsPhpize\Compiler\Exception
     * @throws \JsPhpize\Lexer\Exception
     * @throws \JsPhpize\Parser\Exception
     */
    public function testFunctionNamespace()
    {
        include_once __DIR__ . '/functionInNamespace.php';

        $jsPhpize = new JsPhpize();
        $message = null;

        try {
            $jsPhpize->render('return fooBar()');
        } catch (\JsPhpize\Compiler\Exception $exception) {
            $message = $exception->getMessage();
        }

        $this->assertRegExp('/Undefined variable: fooBar/', $message);

        $jsPhpize = new JsPhpize([
            'functionsNamespace' => 'myNameSpace',
        ]);
        $this->assertSame('Hello', $jsPhpize->render('return fooBar()'));
    }
}
