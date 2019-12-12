<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class RuntimeErrorsTest extends TestCase
{
    /**
     * @group i
     */
    public function testNoParenthesesClose()
    {
        $jsPhpize = new JsPhpize();
        $this->assertSame('8 // 22', $jsPhpize->renderCode('let n = 1;
let i = 22;

for (let i = 0; i < 3; i++) {
    n *= 2;
}

return n + \' // \' + i;'));
    }
}
