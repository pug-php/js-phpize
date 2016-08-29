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

    public function isValue()
    {
        return in_array($this->type, array('variable', 'constant', 'string', 'number'));
    }

    public function isComparison()
    {
        return in_array($this->type, array('===', '!==', '>=', '<=', '<>', '!=', '==', '>', '<'));
    }

    public function isLogical()
    {
        return in_array($this->type, array('&&', '||', '!'));
    }

    public function isBinary()
    {
        return in_array($this->type, array('&', '|', '^', '~', '>>', '<<', '>>>'));
    }

    public function isArithmetic()
    {
        return in_array($this->type, array('+', '-', '/', '*', '%', '**', '--', '++'));
    }

    public function isVarOperator()
    {
        return in_array($this->type, array('delete', 'void', 'typeof'));
    }

    public function isOpener()
    {
        return in_array($this->type, array('{', '(', '['));
    }

    public function isCloser()
    {
        return in_array($this->type, array('}', ')', ']'));
    }

    public function isAssignation()
    {
        return substr($this->type, -1) === '=' && !$this->isComparison();
    }

    public function isOperator()
    {
        return $this->isAssignation() || $this->isComparison() || $this->isArithmetic() || $this->isBinary() || $this->isLogical() || $this->isVarOperator();
    }

    public function expectNoLeftMember()
    {
        return in_array($this->type, array('!', '~')) || $this->isVarOperator();
    }

    public function expectRightMember()
    {
        return $this->isOperator() || $this->isOpener();
    }

    public function canBeFollowedBy($token)
    {
        if (
            !$token ||
            in_array($token->type, array('comment', 'newline')) ||
            in_array($this->type, array('comment', 'newline')) ||
            ($token->type === 'lambda' && !$this->isOperator()) ||
            (!$token->isOperator() && !$this->type === 'lambda')
        ) {
            return true;
        }

        if (in_array(';', array($token->type, $this->type))) {
            return false;
        }

        if ($this->isOpener()) {
            return $token->isValue() || $token->isOpener();
        }

        if ($this->type === 'variable' && in_array($token->type, array('(', '['))) {
            return true;
        }

        if ($this->isValue()) {
            return !$token->expectNoLeftMember() && ($token->type === '.' || $token->isOperator() || $token->isCloser());
        }

        if ($this->isOperator()) {
            return $token->isOpener() || $token->isValue() || in_array($token->type, array('+', '-'));
        }

        return false;
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
