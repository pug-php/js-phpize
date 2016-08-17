<?php

namespace JsPhpize\Parser;

use JsPhpize\JsPhpize;
use JsPhpize\Lexer\Lexer;
use JsPhpize\Nodes\Main;

class Parser extends Visitor
{
    /**
     * @var JsPhpize
     */
    protected $engine;

    /**
     * @var Lexer
     */
    protected $lexer;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var array
     */
    protected $dependencies;

    /**
     * @var array
     */
    protected $stack;

    public function __construct(JsPhpize $engine, $input, $filename)
    {
        $input = str_replace(array("\r\n", "\r"), array("\n", ''), $input);
        $this->tokens = array();
        $this->dependencies = array();
        $this->engine = $engine;
        $this->lexer = new Lexer($engine, $input, $filename);
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function popDependencies()
    {
        $dependencies = $this->getDependencies();
        $this->dependencies = array();

        return $dependencies;
    }

    protected function getHelper($helper)
    {
        if (!isset($this->dependencies[$helper])) {
            $this->dependencies[$helper] = trim(file_get_contents(
                __DIR__ . DIRECTORY_SEPARATOR .
                'Helpers' . DIRECTORY_SEPARATOR .
                ucfirst($helper) . '.h'
            ));
        }

        return '$GLOBALS["' . $this->engine->getOption('varPrefix', JsPhpize::VAR_PREFIX) . 'h_' . $helper . '"]';
    }

    protected function exceptionInfos()
    {
        return $this->lexer->exceptionInfos();
    }

    protected function prepend($token)
    {
        return array_unshift($this->tokens, $token);
    }

    protected function next()
    {
        return array_shift($this->tokens) ?: $this->lexer->next();
    }

    protected function skip($index = 1)
    {
        while ($index--) {
            $this->next();
        }
    }

    protected function advance($index)
    {
        $token = null;

        while ($index--) {
            $token = $this->lexer->next();
            $this->tokens[] = $token;
        }

        return $token;
    }

    protected function get($index)
    {
        return isset($this->tokens[$index])
            ? $this->tokens[$index]
            : $this->advance($index + 1 - count($this->tokens));
    }

    protected function current()
    {
        return $this->get(0);
    }

    protected function unexpected($token)
    {
        throw new Exception('Unexpected ' . $token->type . rtrim(' ' . ($token->value ?: '')) . $this->exceptionInfos(), 8);
    }

    protected function expect($type)
    {
        $token = $this->next();
        if ($token->type !== $type) {
            $this->unexpected($token);
        }

        return $token;
    }

    protected function getCurrentBlock()
    {
        return end($this->stack);
    }

    public function parseBlock($block)
    {
        $this->stack[] = $block;
        $prev = null;
        while ($token = $this->next()) {
            if ($token === $prev) {
                $this->unexpected($token);
            }
            $prev = $token;
            if ($token->type === ';') {
                continue;
            }
            if ($token->type === '}') {
                return;
            }
            $method = 'visit' . ucfirst($token->type);
            $token = method_exists($this, $method)
                ? $this->$method($token)
                : $this->visitNode($token);
            if (!is_array($token)) {
                $token = array($token);
            }
            $block->addNodes($token);
        }
        array_pop($this->stack);
    }

    public function parse()
    {
        $block = new Main($this->engine->getOption('varPrefix', JsPhpize::VAR_PREFIX) . 'h_');
        $this->stack = array();
        $this->parseBlock($block);
        if ($this->next()) {
            $this->unexpected('}');
        }
        $block->addDependencies($this->dependencies);

        return $block;
    }
}
