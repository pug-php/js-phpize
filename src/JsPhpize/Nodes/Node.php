<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parser\Exception;

abstract class Node
{
    public function __get($name)
    {
        return $this->$name;
    }
}
