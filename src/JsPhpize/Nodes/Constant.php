<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parser\Exception;

/**
 * Class Constant.
 *
 * @property-read string $value raw value
 * @property-read string $type  constant type
 */
class Constant extends Value implements Assignable
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $value;

    /**
     * Constant constructor.
     *
     * @param $type
     * @param $value
     *
     * @throws Exception
     */
    public function __construct($type, $value)
    {
        if (!in_array($type, ['constant', 'number', 'string', 'regexp'])) {
            throw new Exception("The given type [$type] is not a valid constant type.", 23);
        }
        $this->type = $type;
        $this->value = $value;
    }

    public function getNonAssignableReason()
    {
        if ($this->type !== 'constant') {
            return "{$this->type} is not assignable.";
        }
        if (in_array($this->value, ['NAN', 'INF'])) {
            return "{$this->value} is not assignable.";
        }
        if (mb_substr($this->value, 0, 2) === 'M_') {
            return "'M_' prefix is reserved to mathematical constants.";
        }

        return false;
    }
}
