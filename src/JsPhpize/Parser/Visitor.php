<?php

namespace JsPhpize\Parser;

use JsPhpize\Nodes\Comment;

class Visitor extends ExpressionParser
{
    protected function visitComment($token)
    {
        return new Comment($token);
    }

    protected function visitNumber($token)
    {
        return $token->value;
    }

    protected function visitVariable($token)
    {
        $variable = (substr($token->value, 0, 1) === '$' ? '' : '$') . $token->value;
        $next = $this->current();
        if ($next->type === '(') {
            $this->skip();

            return $token->value . '(' . $this->getExpression() . ')';
        }
        if ($next->isAssignation()) {
            $this->skip();

            return
                $variable . ' ' .
                $next . ' ' .
                $this->getExpression();
        }
        $afterNext = $this->get(1);
        $variableParts = array($variable);
        while (
            $next && (
                ($parenthesis = ($next->type === '(')) ||
                ($bracket = ($next->type === '[')) || (
                    $afterNext &&
                    $next->type === '.' &&
                    $afterNext->type === 'variable'
                )
            )
        ) {
            $this->skip();
            if ($parenthesis) {
                $variableParts = array('call_user_func(call_user_func(' . $this->getHelper('dot') . ', ' . implode(', ', $variableParts) . '), ' . $this->getExpression() . ')');
            } else {
                $variableParts[] = $bracket ? $this->getExpression() : var_export($afterNext->value, true);
            }
            $this->skip();
            $next = $this->get(0);
            $afterNext = $this->get(1);
        }
        if (count($variableParts) === 1) {
            return $variableParts[0];
        }

        return 'call_user_func(' . $this->getHelper('dot') . ', ' . implode(', ', $variableParts) . ')';
    }

    public function visitReturn($token)
    {
        return 'return ' . $this->getExpression();
    }
}
