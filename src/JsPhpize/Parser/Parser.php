<?php

namespace JsPhpize\Parser;

use JsPhpize\JsPhpize;
use JsPhpize\Lexer\Lexer;
use JsPhpize\Lexer\Token;
use JsPhpize\Nodes\BracketsArray;
use JsPhpize\Nodes\Constant;
use JsPhpize\Nodes\FunctionCall;
use JsPhpize\Nodes\HooksArray;
use JsPhpize\Nodes\Main;
use JsPhpize\Nodes\Variable;

class Parser
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

    /**
     * @var Token
     */
    protected $previousToken;

    public function __construct(JsPhpize $engine, $input, $filename)
    {
        $input = str_replace(array("\r\n", "\r"), array("\n", ''), $input);
        $this->tokens = array();
        $this->dependencies = array();
        $this->engine = $engine;
        $this->lexer = new Lexer($engine, $input, $filename);
    }

    protected function retrieveNext()
    {
        while (($next = $this->lexer->next()) && $next->is('comment'));

        return $next;
    }

    protected function next()
    {
        return array_shift($this->tokens) ?: $this->retrieveNext();
    }

    protected function skip()
    {
        $this->next();
    }

    protected function get($index)
    {
        while ($index >= count($this->tokens)) {
            $this->tokens[] = $this->retrieveNext();
        }

        return $this->tokens[$index];
    }

    protected function exceptionInfos()
    {
        return $this->lexer->exceptionInfos();
    }

    protected function unexpected($token)
    {
        throw new Exception('Unexpected ' . $token->type . rtrim(' ' . ($token->value ?: '')) . $this->exceptionInfos(), 8);
    }

    protected function parseParentheses()
    {
        $parentheses = new Parenthesis();
        $exceptionInfos = $this->exceptionInfos();
        $expectComma = false;
        while ($token = $this->next()) {
            if ($token->is(')')) {
                return $parentheses;
            }
            if ($expectComma) {
                if ($token->is(',')) {
                    $expectComma = false;
                    continue;
                }
                $this->unexpected($token);
            }
            if ($value = $this->getValueFromToken($token)) {
                $expectComma = true;
                $parentheses->addNode($value);
                continue;
            }
            $this->unexpected($token);
        }

        throw new Exception('Missing ) to match ' . $exceptionInfos, 5);
    }

    protected function parseHooksArray()
    {
        $array = new HooksArray();
        $exceptionInfos = $this->exceptionInfos();
        $expectComma = false;
        while ($token = $this->next()) {
            if ($token->is(']')) {
                return $array;
            }
            if ($expectComma) {
                if ($token->is(',')) {
                    $expectComma = false;
                    continue;
                }
                $this->unexpected($token);
            }
            if ($value = $this->getValueFromToken($token)) {
                $expectComma = true;
                $array->addItem($value);
                continue;
            }
            $this->unexpected($token);
        }

        throw new Exception('Missing ] to match ' . $exceptionInfos, 6);
    }

    protected function parseBracketsArray()
    {
        $array = new BracketsArray();
        $exceptionInfos = $this->exceptionInfos();
        $expectComma = false;
        while ($token = $this->next()) {
            if ($token->is('}')) {
                return $array;
            }
            if ($expectComma) {
                if ($token->is(',')) {
                    $expectComma = false;
                    continue;
                }
                $this->unexpected($token);
            }
            if ($token->isValue()) {
                $type = $token->type;
                $value = $token->value;
                if ($type === 'variable') {
                    $type = 'string';
                    $value = var_export($type, true);
                }
                $token = $this->next();
                if (!$token) {
                    throw new Exception('Missing value after ' . $value . $this->exceptionInfos(), 12);
                }
                if (!$token->is(':')) {
                    $this->unexpected($token);
                }
                $key = new Constant($type, $value);
                $value = $this->expectValue($this->next());
                $expectComma = true;
                $array->addItem($key, $value);
            }
            $this->unexpected($token);
        }

        throw new Exception('Missing } to match ' . $exceptionInfos, 7);
    }

    protected function parseVariable($name)
    {
        $children = array();
        while ($next = $this->get(0)) {
            if ($next->is('.')) {
                $this->skip();
                $next = $this->next();

                if ($next->is('variable')) {
                    $children[] = new Constant('string', var_export($next->value, true));

                    continue;
                }

                $this->unexpected($next);
            }

            if ($next->is('[')) {
                $exceptionInfos = $this->exceptionInfos();
                $this->skip();
                $value = $this->expectValue($this->next());

                $next = $this->next();

                if (!$next) {
                    throw new Exception('Missing ] to match ' . $exceptionInfos, 13);
                }

                if (!$next->is(']')) {
                    $this->unexpected($next);
                }

                $children[] = $value;

                continue;
            }

            break;
        }

        return new Variable($name, $children);
    }

    protected function expectValue($next, $exception)
    {
        if (!$next) {
            if ($exception instanceof \Exception) {
                throw $exception;
            }
            if ($exception instanceof Token) {
                $this->unexpected($exception);
            }
            throw new Exception('Value expected before ' . $this->exceptionInfos(), 13);
        }
        $value = $this->getValueFromToken($next);
        if (!$value) {
            $this->unexpected($next);
        }

        return $value;
    }

    protected function parseValue($token)
    {
        $value = $token->is('variable')
            ? $this->parseVariable($token->value)
            : new Constant($token->type, $token->value);

        while ($token = $this->get(0)) {
            if ($token->isValue()) {
                $this->unexpected($this->next());
            }
            if ($token->isOperator()) {
                if ($token->is('{') || $token->expectNoLeftMember()) {
                    $this->unexpected($this->next());
                }
                if ($token->isIn('++', '--')) {
                    $value->append($his->next()->value);

                    continue;
                }
                if ($token->is('(')) {
                    $this->skip();
                    $arguments = array();
                    $value = new FunctionCall($value, $this->parseParentheses()->nodes);

                    continue;
                }
                if ($token->is('?')) {
                    $this->skip();
                    $trueValue = $this->expectValue($this->next());
                    $next = $this->next();
                    if (!$next) {
                        throw new Exception("Ternary expression not properly closed after '?' " . $this->exceptionInfos(), 14);
                    }
                    if (!$next->is(':')) {
                        throw new Exception("':' expected but $next given " . $this->exceptionInfos(), 15);
                    }
                    $next = $this->next();
                    if (!$next) {
                        throw new Exception("Ternary expression not properly closed after ':' " . $this->exceptionInfos(), 16);
                    }
                    $falseValue = $this->expectValue($this->next());
                    $value = new Ternary($value, $trueValue, $falseValue);

                    continue;
                }
                $nextValue = $this->expectValue($this->next());
                $value = new Dyiade($token->value, $value, $nextValue);
            }

            break;
        }

        return $value;
    }

    protected function getValueFromToken($token)
    {
        if ($token->is('(')) {
            return $this->parseParentheses();
        }
        if ($token->is('[')) {
            return $this->parseHooksArray();
        }
        if ($token->is('{')) {
            return $this->parseBracketsArray();
        }
        if ($token->isOperator() && $token->isIn('~', '!', '--', '++', '-', '+', 'delete', 'typeof', 'void')) {
            $value = $this->expectValue($next, $token);
            $value->prepend($token->value);

            return $value;
        }
        if ($token->isValue()) {
            return $this->parseValue($token);
        }
    }

    public function parseBlock($block)
    {
        $this->stack[] = $block;
        $this->previousToken = null;
        $next = $this->get(0);
        if ($next->is('(')) {
            $this->skip();
            $block->setParentheses($this->parseParentheses());
        }
        $next = $this->get(0);
        $waitForBracketToClose = $next->is('{');
        while ($token = $this->next()) {
            if ($token === $this->previousToken) {
                $this->unexpected($token);
            }
            if ($token->is('}') && $waitForBracketToClose) {
                break;
            }
            if ($token->is('keyword')) {
                $block->addInstruction($this->parseBlock());
                continue;
            }
            if ($value = $this->getValueFromToken($token)) {
                $block->addInstruction($value);
                continue;
            }
            if ($token->is(';')) {
                if (!$waitForBracketToClose && !$block instanceof Main) {
                    break;
                }
                $block->endInstruction();
                continue;
            }
            $this->unexpected($token);
        }
        array_pop($this->stack);
    }

    public function parse()
    {
        $block = new Main();
        $this->stack = array();
        $this->parseBlock($block);

        return $block;
    }
}
