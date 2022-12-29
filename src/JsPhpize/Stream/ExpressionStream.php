<?php

namespace JsPhpize\Stream;

use Attribute;

// @codeCoverageIgnoreStart
if (\PHP_VERSION >= 8 && \PHP_VERSION < 8.2 && !class_exists('AllowDynamicProperties')) {
    #[Attribute(Attribute::TARGET_CLASS)]
    final class AllowDynamicProperties
    {
        public function __construct()
        {
        }
    }
}
// @codeCoverageIgnoreEnd

/**
 * Creates a wrapper in order to allow the Zend PhpRenderer
 * to include the compiled file.
 * Class JsPhpize\Stream\ExrpressionStream.
 */
#[\AllowDynamicProperties]
class ExpressionStream
{
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var string
     */
    private $data = '';

    /**
     * @param $path
     *
     * @return bool
     */
    public function stream_open($path)
    {
        $this->data = mb_substr(mb_strstr($path, ';'), 1);

        return true;
    }

    /**
     * @return null
     */
    public function stream_stat()
    {
    }

    /**
     * @param $count
     *
     * @return string
     */
    public function stream_read($count)
    {
        $ret = mb_substr($this->data, $this->position, $count);
        $this->position += mb_strlen($ret);

        return $ret;
    }

    /**
     * @return int
     */
    public function stream_tell()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        return $this->position >= mb_strlen($this->data);
    }

    /**
     * Dummy URL stat method to prevent PHP "undefined method" errors.
     *
     * @return array
     */
    public function url_stat($path, $flags)
    {
        return [0, 0, 0, 0, 0, 0, 0, mb_strlen($this->data), 0, 0, 0, 0];
    }

    /**
     * Dummy option setter.
     *
     * @param $option
     * @param $arg1
     * @param $arg2
     *
     * @return bool
     */
    public function stream_set_option($option, $arg1, $arg2)
    {
        return true;
    }
}
