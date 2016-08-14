<?php

namespace JsPhpize\Nodes;

abstract class ArrayBase
{
    protected $data = array();

    protected function getData()
    {
        return $this->data;
    }

    protected function export($items)
    {
        return 'array( ' . implode(', ', array_map('trim', $items)) . ' )';
    }
}
