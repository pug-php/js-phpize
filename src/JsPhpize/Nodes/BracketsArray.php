<?php

namespace JsPhpize\Nodes;

class BracketsArray extends ArrayBase
{
    public function addItem($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __toString()
    {
        $items = array();
        foreach ($this->data as $key => $value) {
            $items[] = $key . ' => ' . $value;
        }

        return $this->export($items);
    }
}
