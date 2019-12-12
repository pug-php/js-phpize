<?php

use JsPhpize\Stream\ExpressionStream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    /**
     * @group stream
     */
    public function testStreamEmulator()
    {
        $stream = new ExpressionStream();

        $this->assertTrue($stream->stream_open('foo;bar'));
        $this->assertEmpty($stream->stream_stat());
        $this->assertSame('ba', $stream->stream_read(2));
        $this->assertSame(2, $stream->stream_tell());
        $this->assertFalse($stream->stream_eof());
        $this->assertTrue(is_array($stream->url_stat('foo', 0)));
        $this->assertSame('r', $stream->stream_read(2));
        $this->assertTrue($stream->stream_eof());
        $this->assertTrue($stream->stream_set_option(1, 2, 3));
    }
}
