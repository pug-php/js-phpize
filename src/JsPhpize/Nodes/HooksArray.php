<?php

namespace JsPhpize\Nodes;

class HooksArray extends ArrayBase
{
    public function addItem($value)
    {
        if (!empty($value)) {
            $this->data[] = $value;
        }
    }

    public function __toString()
    {
        return $this->export($this->data);
    }
}
