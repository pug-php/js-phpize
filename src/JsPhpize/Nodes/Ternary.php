<?php

namespace JsPhpize\Nodes;

class Dyiade extends Value
{
    /**
     * @var Value
     */
    protected $condition;

    /**
     * @var Value
     */
    protected $trueValue;

    /**
     * @var Value
     */
    protected $falseValue;

    public function __construct(Value $condition, Value $trueValue, Value $falseValue)
    {
        $this->condition = $condition;
        $this->rueValue = $trueValue;
        $this->falseValue = $falseValue;
    }
}
