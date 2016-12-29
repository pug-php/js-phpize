<?php

namespace JsPhpize\Parser;

use JsPhpize\Nodes\Assignation;
use JsPhpize\Nodes\Constant;
use JsPhpize\Nodes\Dyiade;
use JsPhpize\Nodes\FunctionCall;

abstract class TokenExtractor extends TokenCrawler
{
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
                throw $this->unexpected($token);
            }
            $key = new Constant($type, $value);
            $value = $this->expectValue($this->next());

            return array($key, $value);
        }
    }

    protected function getVariableChildFromToken($token)
    {
        if ($token->is('.')) {
            $this->skip();
            $token = $this->next();

            if ($token->is('variable')) {
                return new Constant('string', var_export($token->value, true));
            }

            throw $this->unexpected($token);
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

            throw $this->unexpected($token);
        }
    }

    protected function getEndTokenFromBlock($block)
    {
        return $block->multipleInstructions ? '}' : ';';
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

    protected function getValueFromToken($token)
    {
        $value = $this->getInitialValue($token);
        if ($value) {
            $this->appendFunctionsCalls($value);
        }

        return $value;
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
                throw $this->unexpected($this->next());
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

    protected function expectValue($next, $token = null)
    {
        if (!$next) {
            if ($token) {
                throw $this->unexpected($token);
            }
            throw new Exception('Value expected after ' . $this->exceptionInfos(), 20);
        }
        $value = $this->getValueFromToken($next);
        if (!$value) {
            throw $this->unexpected($next);
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
}
