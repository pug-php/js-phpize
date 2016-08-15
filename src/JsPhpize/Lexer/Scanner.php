<?php

namespace JsPhpize\Lexer;

class Scanner
{
    public function scanComment($matches)
    {
        return $this->valueToken('comment', $matches);
    }

    public function scanConstant($matches)
    {
        $constant = trim($matches[0]);
        if (substr($constant, 0, 5) === '__JP_') {
            throw new Exception('Constants cannot start with __JP_, this prefix is reserved for JsPhpize' . $this->exceptionInfos(), 1);
        }
        $translate = array(
            'Infinity' => 'INF',
            'NaN' => 'NAN',
            'undefined' => 'null',
        );
        if (isset($translate[$constant])) {
            $constant = $translate[$constant];
        } elseif (substr($matches[0], 0, 5) === 'Math.') {
            $constant = 'M_' . substr($constant, 5);
        }
        $this->consume($matches[0]);

        return $this->token('constant', $constant);
    }

    public function scanFunction($matches)
    {
        return $this->valueToken('function', $matches);
    }

    public function scanKeyword($matches)
    {
        return $this->typeToken($matches);
    }

    public function scanLambda($matches)
    {
        return $this->valueToken('lambda', $matches);
    }

    public function scanNumber($matches)
    {
        return $this->valueToken('number', $matches);
    }

    public function scanString($matches)
    {
        return $this->valueToken('string', $matches);
    }

    public function scanOperator($matches)
    {
        return $this->typeToken($matches);
    }

    public function scanVariable($matches)
    {
        if (substr($matches[0], 0, 5) === '__jp_') {
            throw new Exception('Variables cannot start with __jp_, this prefix is reserved for JsPhpize' . $this->exceptionInfos(), 1);
        }

        return $this->valueToken('variable', $matches);
    }
}
