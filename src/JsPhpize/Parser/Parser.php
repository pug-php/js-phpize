<?php

namespace JsPhpize\Parser;

use JsPhpize\JsPhpize;
use JsPhpize\Lexer\Lexer;
use JsPhpize\Nodes\Assignation;
use JsPhpize\Nodes\Block;
use JsPhpize\Nodes\BracketsArray;
use JsPhpize\Nodes\Constant;
use JsPhpize\Nodes\Dyiade;
use JsPhpize\Nodes\FunctionCall;
use JsPhpize\Nodes\HooksArray;
use JsPhpize\Nodes\Main;
use JsPhpize\Nodes\Node;
use JsPhpize\Nodes\Parenthesis;
use JsPhpize\Nodes\Ternary;
use JsPhpize\Nodes\Value;
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
        while (($next = $this->lexer->next()) && $next->isNeutral());

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

    protected function parseLambda(Value $parameters)
    {
        $lambda = new Block('function');
        $lambda->setValue($parameters);
        $next = $this->next();
        if ($next) {
            if ($next->is('{')) {
                $this->parseBlock($lambda);
                $this->skip();

                return $lambda;
            }
            $return = new Block('return');
            $return->setValue($this->expectValue($next));
            $lambda->addInstruction($return);
        }

        return $lambda;
    }

    protected function parseParentheses()
    {
        $parentheses = new Parenthesis();
        $exceptionInfos = $this->exceptionInfos();
        $expectComma = false;
        while ($token = $this->next()) {
            if ($token->is(')')) {
                $next = $this->get(0);
                if ($next && $next->is('lambda')) {
                    $this->skip();

                    return $this->parseLambda($parentheses);
                }

                return $parentheses;
            }
            if ($expectComma) {
                if ($token->isIn(',', ';')) {
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

    protected function getBracketsArrayItemKeyFromToken($token)
    {
        $type = null;

        if ($token->is('keyword')) {
            $type = 'string';
            $value = var_export($token->value, true);
        } elseif ($token->isValue()) {
            $type = $token->type;
            $value = $token->value;
            if ($type === 'variable') {
                $type = 'string';
                $value = var_export($value, true);
            }
        }

        if ($type) {
            $token = $this->next();
            if (!$token) {
                throw new Exception('Missing value after ' . $value . $this->exceptionInfos(), 12);
            }
            if (!$token->is(':')) {
                $this->unexpected($token);
            }
            $key = new Constant($type, $value);
            $value = $this->expectValue($this->next());

            return array($key, $value);
        }
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
            if ($pair = $this->getBracketsArrayItemKeyFromToken($token)) {
                list($key, $value) = $pair;
                $expectComma = true;
                $array->addItem($key, $value);

                continue;
            }
            $this->unexpected($token);
        }

        throw new Exception('Missing } to match ' . $exceptionInfos, 7);
    }

    protected function getVariableChildFromToken($token)
    {
        if ($token->is('.')) {
            $this->skip();
            $token = $this->next();

            if ($token->is('variable')) {
                return new Constant('string', var_export($token->value, true));
            }

            $this->unexpected($token);
        }

        if ($token->is('[')) {
            $exceptionInfos = $this->exceptionInfos();
            $this->skip();
            $value = $this->expectValue($this->next());

            $token = $this->next();

            if (!$token) {
                throw new Exception('Missing ] to match ' . $exceptionInfos, 13);
            }

            if ($token->is(']')) {
                return $value;
            }

            $this->unexpected($token);
        }
    }

    protected function parseVariable($name)
    {
        $children = array();
        while ($next = $this->get(0)) {
            if ($next->is('lambda')) {
                $this->skip();
                $parenthesis = new Parenthesis();
                $parenthesis->addNode(new Variable($name, $children));

                return $this->parseLambda($parenthesis);
            }

            if ($value = $this->getVariableChildFromToken($next)) {
                $children[] = $value;

                continue;
            }

            break;
        }

        $variable = new Variable($name, $children);

        for ($i = count($this->stack) - 1; $i >= 0; $i--) {
            $block = $this->stack[$i];
            if ($block->isLet($name)) {
                $variable->setScope($block);

                break;
            }
        }

        return $variable;
    }

    protected function expectValue($next, $token = null)
    {
        if (!$next) {
            if ($token) {
                $this->unexpected($token);
            }
            throw new Exception('Value expected after ' . $this->exceptionInfos(), 20);
        }
        $value = $this->getValueFromToken($next);
        if (!$value) {
            $this->unexpected($next);
        }

        return $value;
    }

    protected function parseTernary(Node $condition)
    {
        $trueValue = $this->expectValue($this->next());
        $next = $this->next();
        if (!$next) {
            throw new Exception("Ternary expression not properly closed after '?' " . $this->exceptionInfos(), 14);
        }
        if (!$next->is(':')) {
            throw new Exception("':' expected but " . ($next->value ?: $next->type) . ' given ' . $this->exceptionInfos(), 15);
        }
        $next = $this->next();
        if (!$next) {
            throw new Exception("Ternary expression not properly closed after ':' " . $this->exceptionInfos(), 16);
        }
        $falseValue = $this->expectValue($next);
        $next = $this->get(0);

        return new Ternary($condition, $trueValue, $falseValue);
    }

    protected function parseValue($token)
    {
        return $token->is('variable')
            ? $this->parseVariable($token->value)
            : new Constant($token->type, $token->value);
    }

    protected function parseFunction($token)
    {
        $function = new Block('function');
        $token = $this->get(0);
        if ($token->is('variable')) {
            $this->skip();
            $token = $this->get(0);
        }
        if (!$token->is('(')) {
            $this->unexpected($token);
        }
        $this->skip();
        $function->setValue($this->parseParentheses());
        $token = $this->get(0);
        if (!$token->is('{')) {
            $this->unexpected($token);
        }
        $this->skip();
        $this->parseBlock($function);
        $this->skip();

        return $function;
    }

    protected function getInitialValue($token)
    {
        if ($token->is('function')) {
            return $this->parseFunction($token);
        }
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
            $value = $this->expectValue($this->next(), $token);
            $value->prepend($token->type);

            return $value;
        }
        if ($token->isValue()) {
            return $this->parseValue($token);
        }
    }

    protected function appendFunctionsCalls(&$value)
    {
        while ($token = $this->get(0)) {
            if ($token->is('{') || $token->expectNoLeftMember()) {
                $this->unexpected($this->next());
            }
            if ($token->is('?')) {
                $this->skip();
                $value = $this->parseTernary($value);

                continue;
            }
            if ($token->is('(')) {
                $this->skip();
                $arguments = array();
                $value = new FunctionCall($value, $this->parseParentheses()->nodes);

                continue;
            }
            if ($token->isOperator()) {
                if ($token->isIn('++', '--')) {
                    $value->append($this->next()->type);

                    break;
                }
                if ($token->isAssignation()) {
                    $this->skip();
                    $arguments = array();
                    $valueToAssign = $this->expectValue($this->next());
                    $value = new Assignation($token->type, $value, $valueToAssign);

                    continue;
                }

                $this->skip();
                $nextValue = $this->expectValue($this->next());
                $value = new Dyiade($token->type, $value, $nextValue);
                $token = $this->get(0);

                continue;
            }

            break;
        }
    }

    protected function getValueFromToken($token)
    {
        $value = $this->getInitialValue($token);
        if ($value) {
            $this->appendFunctionsCalls($value);
        }

        return $value;
    }

    protected function expectColon($errorMessage, $errorCode)
    {
        $colon = $this->next();
        if (!$colon || !$colon->is(':')) {
            throw new Exception($errorMessage, $errorCode);
        }
    }

    protected function handleOptionalValue($keyword, $afterKeyword)
    {
        if (!$afterKeyword->is(';')) {
            $value = $this->expectValue($this->next());
            $keyword->setValue($value);
        }
    }

    protected function handleParentheses($keyword, $afterKeyword)
    {
        if ($afterKeyword && $afterKeyword->is('(')) {
            $this->skip();
            $keyword->setValue($this->parseParentheses());
        } elseif ($keyword->needParenthesis()) {
            throw new Exception("'" . $keyword->type . "' block need parentheses.", 17);
        }
    }

    protected function parseKeywordStatement($token)
    {
        $name = $token->value;
        $keyword = new Block($name);
        switch ($name) {
            case 'return':
            case 'continue':
            case 'break':
                $this->handleOptionalValue($keyword, $this->get(0));
                break;
            case 'case':
                $value = $this->expectValue($this->next());
                $keyword->setValue($value);
                $this->expectColon("'case' must be followed by a value and a colon.", 21);
                break;
            case 'default':
                $this->expectColon("'default' must be followed by a colon.", 22);
                break;
            default:
                $this->handleParentheses($keyword, $this->get(0));
        }

        return $keyword;
    }

    protected function parseKeyword($token)
    {
        $keyword = $this->parseKeywordStatement($token);
        if ($keyword->handleInstructions()) {
            $this->parseBlock($keyword);
        }

        return $keyword;
    }

    protected function parseLet($token)
    {
        $letVariable = $this->get(0);
        if (!$letVariable->is('variable')) {
            $this->unexpected($letVariable, $token);
        }

        return $letVariable->value;
    }

    protected function getInstructionFromToken($token)
    {
        if ($token->is('keyword')) {
            return $this->parseKeyword($token);
        }

        if ($value = $this->getValueFromToken($token)) {
            return $value;
        }
    }

    protected function getEndTokenFromBlock($block)
    {
        return $block->multipleInstructions ? '}' : ';';
    }

    protected function parseInstructions($block)
    {
        $endToken = $this->getEndTokenFromBlock($block);
        while ($token = $this->next()) {
            if ($token->is($endToken)) {
                break;
            }
            if ($token->is('var')) {
                continue;
            }
            if ($token->is('let')) {
                $block->let($this->parseLet($token));
                continue;
            }
            if ($instruction = $this->getInstructionFromToken($token)) {
                $block->addInstruction($instruction);
                continue;
            }
            if ($token->is(';')) {
                $block->endInstruction();
                continue;
            }
            $this->unexpected($token);
        }
    }

    public function parseBlock($block)
    {
        $this->stack[] = $block;
        $next = $this->get(0);
        if ($next && $next->is('(')) {
            $this->skip();
            $block->setValue($this->parseParentheses());
        }
        if (!$block->multipleInstructions) {
            $next = $this->get(0);
            if ($next && $next->is('{')) {
                $block->enableMultipleInstructions();
            }
            if ($block->multipleInstructions) {
                $this->skip();
            }
        }
        $this->parseInstructions($block);
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
