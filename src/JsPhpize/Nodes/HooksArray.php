<?php

namespace JsPhpize\Nodes;

class HooksArray extends ArrayBase
{
    public function addItem($value)
    {
        $this->data[] = $value;
    }

    public function __toString()
    {
        return $this->export($this->data);
    }
}
