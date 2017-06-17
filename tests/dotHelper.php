<?php

use JsPhpize\JsPhpize;

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

class MagicCallMethodObject
{
    public function __call($name, array $args)
    {
        if ($name === 'bar') {
            return 'biz';
        }
    }
}
class SemiMagicMethodObject
{
    public function __get($name)
    {
        if ($name === 'bar') {
            return 'biz';
        }
    }
}
class ArrayAccessObject implements \ArrayAccess
{
    protected $data = array(
        'foo' => 'bar',
    );

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

class DotHelperTest extends \PHPUnit_Framework_TestCase
{
    protected function getDotHelper()
    {
        return eval('return ' . file_get_contents(__DIR__ . '/../src/JsPhpize/Compiler/Helpers/Dot.h') . ';');
    }

    public function testArrayValue()
    {
        $dotHelper = $this->getDotHelper();

        $this->assertSame(42, $dotHelper(array(
                'foo' => 42,
            ), 'foo'));
        $this->assertSame('biz', $dotHelper(array(
                'foo' => array(
                    'bar' => 'biz',
                ),
            ), 'foo', 'bar'));
        $this->assertSame(null, $dotHelper(array(
                'foo' => array(
                ),
            ), 'foo', 'bar'));
        $this->assertSame(null, $dotHelper(array(
                'foo' => array(
                ),
            ), 'biz', 'bar'));
    }

    public function testObjectMember()
    {
        $dotHelper = $this->getDotHelper();

        $this->assertSame(42, $dotHelper((object) array(
                'foo' => 42,
            ), 'foo'));
        $this->assertSame('biz', $dotHelper((object) array(
                'foo' => (object) array(
                    'bar' => 'biz',
                ),
            ), 'foo', 'bar'));
        $this->assertSame(null, $dotHelper((object) array(
                'foo' => (object) array(
                ),
            ), 'foo', 'bar'));
        $this->assertSame(null, $dotHelper((object) array(
                'foo' => (object) array(
                ),
            ), 'biz', 'bar'));
    }

    public function testMixedObjectMemberAndArayValue()
    {
        $dotHelper = $this->getDotHelper();

        $this->assertSame('biz', $dotHelper(array(
                'foo' => (object) array(
                    'bar' => 'biz',
                ),
            ), 'foo', 'bar'));
        $this->assertSame('biz', $dotHelper((object) array(
                'foo' => array(
                    'bar' => 'biz',
                ),
            ), 'foo', 'bar'));
        $this->assertSame(42, $dotHelper((object) array(
                'foo' => array(
                    'bar' => (object) array(
                        'biz' => 42,
                    ),
                ),
            ), 'foo', 'bar', 'biz'));
        $this->assertSame(42, $dotHelper(array(
                'foo' => (object) array(
                    'bar' => array(
                        'biz' => 42,
                    ),
                ),
            ), 'foo', 'bar', 'biz'));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testUndefinedProperties()
    {
        $dotHelper = $this->getDotHelper();
        $object = new MagicCallMethodObject();
        $dotHelper($object, 'foo');
    }

    public function testMagicMethod()
    {
        $dotHelper = $this->getDotHelper();
        $object = new MagicMethodObject();
        $partialObject = new SemiMagicMethodObject();

        $this->assertSame($object->foo, $dotHelper($object, 'foo'));
        // should return `null` given the class's `__get` returns nothing
        $this->assertSame($object->nonexistent, $dotHelper($object, 'nonexistent'));
        $this->assertSame($partialObject->bar, $dotHelper($partialObject, 'bar')); //biz
        $this->assertSame('biz', call_user_func($dotHelper($object, 'bar')));
        $this->assertSame(null, call_user_func($dotHelper($object, 'biz')));

        $jsPhpize = new JsPhpize(array(
            'returnLastStatement' => true,
        ));
        $hello = $jsPhpize->render('__page.seo.title', array(
            '__page' => array(
                'seo' => array(
                    'title' => 'Hello',
                ),
            ),
        ));

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
        $jsPhpize = new JsPhpize(array(
            'helpers' => array(
                'plus' => $plusHelper,
            ),
            'returnLastStatement' => true,
        ));

        $this->assertEquals(18, $jsPhpize->render('3 + 6'));

        $jsPhpize = new JsPhpize(array(
            'helpers' => array(
                'dot' => 'plus',
            ),
            'returnLastStatement' => true,
        ));

        $this->assertEquals('xb', $jsPhpize->render('a.b', array(
            'a' => 'x',
        )));
    }

    public function testDotObjectHelper()
    {
        $jsPhpize = new JsPhpize(array(
            'helpers' => array(
                'dot' => 'dotObject',
            ),
            'returnLastStatement' => true,
        ));

        $this->assertEquals('hello', $jsPhpize->render('a.hello', array(
            'a' => new MagicGetterWithNoIsset(),
        )));
    }
}
