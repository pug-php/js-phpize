<?php

namespace JsPhpize\Nodes;

class Variable extends Value implements Assignable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $children;

    public function __construct($name, array $children)
    {
        $this->name = $name;
        $this->children = $children;
    }

    public function getNonAssignableReason() {}

    public function isAssignable()
    {
        return true;
    }
}
