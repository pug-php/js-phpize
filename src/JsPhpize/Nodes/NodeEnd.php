<?php

namespace JsPhpize\Nodes;

class NodeEnd
{
    protected $token;

    public function __construct($token = null)
    {
        $this->token = $token;
    }

    public function __toString()
    {
        return '';
    }
}
