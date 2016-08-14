<?php

namespace JsPhpize\Lexer;

class Token
{
    /**
     * @var array
     */
    protected $data;

    public function __construct($type, array $data)
    {
        $this->data = array_merge(array(
            'type' => $type,
        ), $data);
    }

    public function isComparison()
    {
        return in_array($this->type, array('===', '!==', '>=', '<=', '<>', '!=', '==', '>', '<'));
    }

    public function isAssignation()
    {
        return substr($this->type, -1) === '=' && !$this->isComparison();
    }

    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function __toString()
    {
        return $this->value ?: $this->type;
    }
}
