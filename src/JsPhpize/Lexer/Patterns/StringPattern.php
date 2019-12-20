<?php

namespace JsPhpize\Lexer\Patterns;

use Generator;
use JsPhpize\Lexer\Lexer;
use JsPhpize\Lexer\Pattern;
use JsPhpize\Parser\Exception;

class StringPattern extends Pattern
{
    protected $patterns = [
        '"' => '"(?:\\\\.|[^"\\\\])*"',
        "'" => "'(?:\\\\.|[^'\\\\])*'",
        '`' => '`(?:\\\\.|[^`\\\\])*`',
    ];

    public function __construct($priority)
    {
        parent::__construct($priority, 'string', implode('|', $this->patterns));
    }

    public function lexWith(Lexer $lexer): Generator
    {
        $string = $this->getBackTickString($lexer->rest());

        if ($string === '') {
            return parent::lexWith($lexer);
        }

        yield $lexer->consumeStringToken($string);
    }

    private function getBackTickString(string $input): string
    {
        if (!preg_match('/^\s*`/', $input)) {
            return '';
        }

        $string = '';

        while (preg_match('/^(.*)(?:(\\\\|\\${).*)?$/U', $input, $interpolation)) {
            $string .= $interpolation[1];
            $input = substr($input, strlen($interpolation[1]));

            if (isset($interpolation[2])) {
                $bracketCount = 1;

                while ($bracketCount) {
                    if (preg_match('/^[^"\'`{}]+/', $input, $match)) {
                        $string .= $match[0];
                        $input = substr($input, strlen($match[0]));
                    }

                    $char = substr($input, 0, 1);

                    switch ($char) {
                        case '"':
                        case "'":
                            if (!preg_match('/^' . $this->patterns[$char] . '/', $input, $match)) {
                                throw new Exception("Unterminated $char string after $string");
                            }

                            $string .= $match[0];
                            $input = substr($input, strlen($match[0]));
                            break;
                        case '`':
                            $backTickString = $this->getBackTickString($input);
                            $string .= $backTickString;
                            $input = substr($input, strlen($backTickString));
                            break;
                        case '{':
                            $bracketCount++;
                            $string .= '{';
                            $input = substr($input, 1);
                            break;
                        case '}':
                            $bracketCount--;
                            $string .= '}';
                            $input = substr($input, 1);

                            if (!$bracketCount) {
                                break 2;
                            }

                            break;
                        default:
                            break 2;
                    }
                }

                if ($bracketCount) {
                    throw new Exception("Broken interpolation after $string");
                }
            }
        }

        if (!preg_match('/^(.*)`/', $input, $match)) {
            throw new Exception("Unterminated ` string after $string");
        }

        return $string . $match[0];
    }
}
