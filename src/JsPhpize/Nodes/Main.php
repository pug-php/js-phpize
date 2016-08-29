<?php

namespace JsPhpize\Nodes;

class Main extends Block
{
    /**
     * @var string
     */
    protected $helperPrefix;

    public function __construct($helperPrefix, $parentheses = null)
    {
        $this->helperPrefix = $helperPrefix;
        parent::__construct('main', $parentheses);
    }
}
