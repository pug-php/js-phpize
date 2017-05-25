<?php

namespace JsPhpize;

abstract class Readable
{
    public function __get($name)
    {
        return $this->$name;
    }
}
