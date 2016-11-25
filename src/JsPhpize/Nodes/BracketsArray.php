<?php

namespace JsPhpize\Nodes;

class BracketsArray extends ArrayBase
{
    public function addItem(Constant $key, Value $value)
    {
        $this->data[] = array($key, $value);
    }
}
