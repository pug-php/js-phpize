<?php

namespace JsPhpize\Lexer;

use JsPhpize\JsPhpize;

class Lexer extends Scanner
{
    /**
     * @var string
     */
    protected $input;

    /**
     * @var JsPhpize
     */
    protected $engine;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $fileInfo = null;

    /**
     * @var string
     */
    protected $consumed = '';

    public function __construct(JsPhpize $engine, $input, $filename)
    {
        $this->engine = $engine;
        $this->filename = $filename;
        $this->line = 1;
        $disallow = $engine->getOption('disallow', []);
        if (is_string($disallow)) {
            $disallow = explode(' ', $disallow);
        }
        $this->disallow = array_map('strtolower', (array) $disallow);
        $this->input = trim($input);
    }

    public function exceptionInfos()
    {
        if (is_null($this->fileInfo)) {
            $this->fileInfo = $this->filename ? ' in ' . realpath($this->filename) : '';
        }

        return
            $this->fileInfo .
            ' on line ' . $this->line .
            ' near from ' . trim($this->consumed);
    }

    protected function consume($consumed)
    {
        $consumed = is_int($consumed) ? mb_substr($this->input, 0, $consumed) : $consumed;
        $this->consumed = mb_strlen(trim($consumed)) > 1 ? $consumed : $this->consumed . $consumed;
        $this->line += mb_substr_count($consumed, "\n");
        $this->input = mb_substr($this->input, mb_strlen($consumed));
    }

    protected function token($type, $data = [])
    {
        $className = $this->engine->getOption('tokenClass', '\\JsPhpize\\Lexer\\Token');

        return new $className($type, is_string($data) ? ['value' => $data] : (array) $data);
    }

    protected function typeToken($matches)
    {
        $this->consume($matches[0]);

        return $this->token(trim($matches[0]));
    }

    protected function valueToken($type, $matches)
    {
        $this->consume($matches[0]);

        return $this->token($type, trim($matches[0]));
    }

    protected function scan($pattern, $method)
    {
        if (preg_match('/^\s*(' . $pattern . ')/', $this->input, $matches)) {
            return $this->{'scan' . ucfirst($method)}($matches);
        }

        return false;
    }

    /**
     * Return a unexpected exception for a given token.
     *
     * @param $token
     *
     * @return Exception
     */
    public function unexpected($token, $className = '\\JsPhpize\\Lexer\\Exception')
    {
        return new $className('Unexpected ' . $token->type . rtrim(' ' . ($token->value ?: '')) . $this->exceptionInfos(), 8);
    }

    /**
     * @throws Exception
     *
     * @return Token|false
     */
    public function next()
    {
        if (!mb_strlen($this->input)) {
            return false;
        }

        foreach ($this->engine->getPatterns() as $pattern) {
            if ($token = $this->scan($pattern->regex, $pattern->type)) {
                if (in_array($pattern->type, $this->disallow)) {
                    throw new Exception($pattern->type . ' is disallowed.', 3);
                }

                return $token;
            }
        }

        throw new Exception('Unknow pattern found at: ' . mb_substr($this->input, 0, 100), 12);
    }
}
