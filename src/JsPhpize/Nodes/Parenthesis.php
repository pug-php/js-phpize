<?php

namespace JsPhpize\Nodes;

class Parenthesis extends Block
{
    public function __toString()
    {
        return implode(' ', $this->nodes);
    }
}
