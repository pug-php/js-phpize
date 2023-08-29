<?php

namespace JsPhpize\Lexer;

class DataBag
{
    /**
     * @var array
     */
    protected $data;

    public function __construct($type, array $data)
    {
        $this->data = array_merge([
            'type' => $type,
        ], $data);
    }

    public function is($value)
    {
        return in_array($value, [$this->type, $this->value], true);
    }

    public function typeIn($values)
    {
        return in_array($this->type, $values, true);
    }

    public function valueIn($values)
    {
        return in_array($this->value, $values, true);
    }

    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}
