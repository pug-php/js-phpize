<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class MagicMethodObject
{
    public function __get($name)
    {
        if ($name === 'foo') {
            return 'bar';
        }
    }

    public function __call($name, array $args)
    {
        if ($name === 'bar') {
            return 'biz';
        }
    }

    public function __isset($name)
    {
        return $name === 'foo';
    }
}

class ArrayAccessObject implements \ArrayAccess
{
    protected $data = [
        'foo' => 'bar',
    ];

    public function offsetGet($name)
    {
        return $this->data[$name];
    }

    public function offsetExists($name)
    {
        return isset($this->data[$name]);
    }

    public function offsetSet($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function offsetUnset($name)
    {
        unset($this->data[$name]);
    }
}

class MagicGetterWithNoIsset
{
    public function __get($name)
    {
        return $name;
    }
}

class DotHelperTest extends TestCase
{
    protected function getDotHelper()
    {
        return eval('return ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Dot.h') . ';');
    }

    public function testArrayValue()
    {
        $dotHelper = $this->getDotHelper();

        $this->assertSame(42, $dotHelper([
            'foo' => 42,
        ], 'foo'));
        $this->assertSame('biz', $dotHelper([
            'foo' => [
                'bar' => 'biz',
            ],
        ], 'foo', 'bar'));
        $this->assertSame(null, $dotHelper([
            'foo' => [
            ],
        ], 'foo', 'bar'));
        $this->assertSame(null, $dotHelper([
            'foo' => [
            ],
        ], 'biz', 'bar'));
    }

    public function testObjectMember()
    {
        $dotHelper = $this->getDotHelper();

        $this->assertSame(42, $dotHelper((object) [
            'foo' => 42,
        ], 'foo'));
        $this->assertSame('biz', $dotHelper((object) [
            'foo' => (object) [
                'bar' => 'biz',
            ],
        ], 'foo', 'bar'));
        $this->assertSame(null, $dotHelper((object) [
            'foo' => (object) [
            ],
        ], 'foo', 'bar'));
        $this->assertSame(null, $dotHelper((object) [
            'foo' => (object) [
            ],
        ], 'biz', 'bar'));
    }

    public function testMixedObjectMemberAndArayValue()
    {
        $dotHelper = $this->getDotHelper();

        $this->assertSame('biz', $dotHelper([
            'foo' => (object) [
                'bar' => 'biz',
            ],
        ], 'foo', 'bar'));
        $this->assertSame('biz', $dotHelper((object) [
            'foo' => [
                'bar' => 'biz',
            ],
        ], 'foo', 'bar'));
        $this->assertSame(42, $dotHelper((object) [
            'foo' => [
                'bar' => (object) [
                    'biz' => 42,
                ],
            ],
        ], 'foo', 'bar', 'biz'));
        $this->assertSame(42, $dotHelper([
            'foo' => (object) [
                'bar' => [
                    'biz' => 42,
                ],
            ],
        ], 'foo', 'bar', 'biz'));
    }

    public function testMagicMethod()
    {
        $dotHelper = $this->getDotHelper();
        $object = new MagicMethodObject();

        $this->assertSame('bar', $dotHelper($object, 'foo'));
        $this->assertSame('biz', call_user_func($dotHelper($object, 'bar')));
        $this->assertSame(null, call_user_func($dotHelper($object, 'biz')));

        $jsPhpize = new JsPhpize([
            'returnLastStatement' => true,
        ]);
        $hello = $jsPhpize->render('__page.seo.title', [
            '__page' => [
                'seo' => [
                    'title' => 'Hello',
                ],
            ],
        ]);

        $this->assertSame('Hello', $hello);
    }

    public function testArrayAccess()
    {
        $dotHelper = $this->getDotHelper();
        $object = new ArrayAccessObject();

        $this->assertSame('bar', $dotHelper($object, 'foo'));
        $this->assertSame(null, $dotHelper($object, 'biz'));
    }

    public function testCustomHelper()
    {
        $plusHelper = 'function ($base) {
            foreach (array_slice(func_get_args(), 1) as $value) {
                $base = $base * $value;
            }
        
            return $base;
        }';
        $jsPhpize = new JsPhpize([
            'helpers' => [
                'plus' => $plusHelper,
            ],
            'returnLastStatement' => true,
        ]);

        $this->assertEquals(18, $jsPhpize->render('3 + 6'));

        $jsPhpize = new JsPhpize([
            'helpers' => [
                'dot' => 'plus',
            ],
            'returnLastStatement' => true,
        ]);

        $this->assertEquals('xb', $jsPhpize->render('a.b', [
            'a' => 'x',
        ]));
    }

    public function testDotObjectHelper()
    {
        $jsPhpize = new JsPhpize([
            'helpers' => [
                'dot' => 'dotObject',
            ],
            'returnLastStatement' => true,
        ]);

        $this->assertEquals('hello', $jsPhpize->render('a.hello', [
            'a' => new MagicGetterWithNoIsset(),
        ]));
    }

    public function testHelperFile()
    {
        $jsPhpize = new JsPhpize([
            'helpers' => [
                'plus' => __DIR__ . '/Plus.h',
            ],
            'returnLastStatement' => true,
        ]);

        $this->assertEquals(6, $jsPhpize->render('13 + 7'));
    }

    public function testDotHelperWithArrayPrototype()
    {
        $jsPhpize = new JsPhpize([
            'helpers' => [
                'dot' => 'dotWithArrayPrototype',
            ],
            'returnLastStatement' => true,
        ]);

        $this->assertSame(
            '1,3',
            implode(',', $jsPhpize->render('a = [1,2,3]; a.filter(function (i) { return i % 2; })'))
        );

        $jsPhpize = new JsPhpize([
            'helpers' => [
                'dot' => 'dotWithArrayPrototype',
            ],
        ]);

        $items = [1, 2, 3];
        ob_start();
        eval($jsPhpize->compile('items.forEach(function (item) { echo(item); })'));
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame(
            '123',
            $actual
        );

        $jsPhpize = new JsPhpize([
            'helpers' => [
                'dot' => 'dotWithArrayPrototype',
            ],
            'allowTruncatedParentheses' => true,
        ]);

        ob_start();
        $items = [2, 4, 6];
        eval(
            $jsPhpize->compile('items.forEach(function (item) {') .
            ' ?><?php echo ' . $jsPhpize->compile('item') . ' ?><?php ' .
            $jsPhpize->compile('})') .
            ' ?>'
        );
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame(
            '246',
            $actual
        );

        $jsPhpize = new JsPhpize();

        $jsPhpize->setFlag(JsPhpize::FLAG_TRUNCATED_PARENTHESES, true);

        self::assertEquals(true, $jsPhpize->hasFlag(JsPhpize::FLAG_TRUNCATED_PARENTHESES));

        $jsPhpize->setFlag(JsPhpize::FLAG_TRUNCATED_PARENTHESES, false);

        self::assertEquals(false, $jsPhpize->hasFlag(JsPhpize::FLAG_TRUNCATED_PARENTHESES));
    }
}
