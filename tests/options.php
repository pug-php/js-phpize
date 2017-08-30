<?php

use JsPhpize\JsPhpize;
use JsPhpize\Lexer\Pattern;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group disallow
     */
    public function testDisallow()
    {
        $jsPhpize = new JsPhpize(array(
            'disallow' => 'foo bar',
        ));
        $this->assertSame(6, $jsPhpize->render('
            var a = 3;
            for (i = 0; i < 3; i++) {
                a++; // increment
            }
            return a;
        '));

        $jsPhpize = new JsPhpize(array(
            'disallow' => 'foo comment bar',
        ));
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
        $jsPhpize = new JsPhpize(array(
            'constPrefix' => 'FOO',
        ));
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
        $jsPhpize = new JsPhpize(array(
            'varPrefix' => 'test',
        ));
        $jsPhpize->render('
            var a = test_zz;
        ');
    }

    /**
     * @group return
     */
    public function testReturnLastStatement()
    {
        $jsPhpize = new JsPhpize(array(
            'returnLastStatement' => true,
        ));
        $eleven = $jsPhpize->render('
            var a = 8;
            a + 3;
        ');
        $this->assertSame(11, $eleven);

        $jsPhpize = new JsPhpize(array(
            'returnLastStatement' => false,
        ));
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
        $jsPhpize = new JsPhpize(array(
            'tokenClass' => 'TestToken',
        ));
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
        $jsPhpize = new JsPhpize(array(
            'tokenClass' => 'TestToken',
        ));
        $jsPhpize->removePatterns(function (Pattern $pattern) {
            return !in_array($pattern->type, array('number', 'operator'));
        });
        $code = $jsPhpize->compile('1 + 1');
    }
}
