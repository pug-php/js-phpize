<?php

namespace JsPhpize;

use JsPhpize\Compiler\Compiler;
use JsPhpize\Compiler\Exception;
use JsPhpize\Parser\Parser;

class JsPhpize extends JsPhpizeOptions
{
    const FLAG_TRUNCATED_PARENTHESES = 1;

    /**
     * @var string
     */
    protected $stream = 'jsphpize.stream';

    /**
     * @var array
     */
    protected $streamsRegistered = array();

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var array
     */
    protected $dependencies = array();

    /**
     * @var array
     */
    protected $sharedVariables = array();

    /**
     * @var int
     */
    protected $flags = 0;

    /**
     * Compile file or code (detect if $input is an exisisting file, else use it as content).
     *
     * @param string $input    file or content
     * @param string $filename if specified, input is used as content and filename as its name
     *
     * @return string
     */
    public function compile($input, $filename = null)
    {
        $this->flags = 0;

        if ($filename === null) {
            $filename = file_exists($input) ? $input : null;
            $input = $filename === null ? $input : file_get_contents($filename);
        }

        $start = '';
        $end = '';
        if (preg_match('/^([)}\]\s]*)(.*?)([({\[\s]*)$/', trim($input), $match)) {
            list(, $start, $input, $end) = $match;
        }

        $parser = new Parser($this, $input, $filename);
        $compiler = new Compiler($this);
        $block = $parser->parse();
        $php = $compiler->compile($block);

        if ($this->flags & self::FLAG_TRUNCATED_PARENTHESES) {
            $php = preg_replace('/\)[\s;]*$/', '', $php);
        }

        if (mb_substr(ltrim($end), 0, 1) === '{') {
            $php = preg_replace('/\s*\{\s*\}\s*$/', '', $php);
        }

        $dependencies = $compiler->getDependencies();
        if ($this->getOption('catchDependencies')) {
            $this->dependencies = array_unique(array_merge($this->dependencies, $dependencies));
            $dependencies = array();
        }

        $php = $compiler->compileDependencies($dependencies) . $start . $php . $end;

        return preg_replace('/\{(\s*\}\s*\{)+$/', '{', $php);
    }

    /**
     * @param int  $flag    flag to set
     * @param bool $enabled flag state
     */
    public function setFlag($flag, $enabled = true)
    {
        if ($enabled) {
            $this->flags |= $flag;

            return;
        }

        $this->flags &= ~$flag;
    }

    /**
     * Compile a file.
     *
     * @param string $file input file
     *
     * @return string
     */
    public function compileFile($file)
    {
        return $this->compile(file_get_contents($file), $file);
    }

    /**
     * Compile raw code.
     *
     * @param string $code input code
     *
     * @return string
     */
    public function compileCode($code)
    {
        return $this->compile($code, 'source.js');
    }

    /**
     * Return compiled dependencies caught during previous compilations.
     *
     * @return string
     */
    public function compileDependencies()
    {
        $compiler = new Compiler($this);

        return $compiler->compileDependencies($this->dependencies);
    }

    /**
     * Flush all saved dependencies.
     *
     * @return $this
     */
    public function flushDependencies()
    {
        $this->dependencies = array();

        return $this;
    }

    /**
     * Compile and return the code execution result.
     *
     * @param string $input     file or content
     * @param string $filename  if specified, input is used as content and filename as its name
     * @param array  $variables variables to be used in rendered code
     *
     * @return mixed
     */
    public function render($input, $filename = null, array $variables = array())
    {
        if (is_array($filename)) {
            $variables = $filename;
            $filename = null;
        }
        if (!in_array($this->stream, $this->streamsRegistered)) {
            $this->streamsRegistered[] = $this->stream;
            if (in_array($this->stream, stream_get_wrappers())) {
                stream_wrapper_unregister($this->stream);
            }
            $classParts = explode('\\', get_class($this));
            stream_wrapper_register($this->stream, $classParts[0] . '\Stream\ExpressionStream');
        }

        extract(array_merge($this->sharedVariables, $variables));

        try {
            return include $this->stream . '://data;<?php ' . $this->compile($input, $filename);
        } catch (\JsPhpize\Compiler\Exception $exception) {
            throw $exception;
        } catch (\JsPhpize\Lexer\Exception $exception) {
            throw $exception;
        } catch (\JsPhpize\Parser\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $summary = $input;
            if (mb_strlen($summary) > 50) {
                $summary = mb_substr($summary, 0, 47) . '...';
            }

            throw new Exception(
                "An error occur in [$summary]:\n" . $exception->getMessage(),
                2,
                E_ERROR,
                __FILE__,
                __LINE__,
                $exception
            );
        }
    }

    /**
     * Render a file.
     *
     * @param string $file      input file
     * @param array  $variables variables to be used in rendered code
     *
     * @return string
     */
    public function renderFile($file, array $variables = array())
    {
        return $this->render(file_get_contents($file), $file, $variables);
    }

    /**
     * Render raw code.
     *
     * @param string $code      input code
     * @param array  $variables variables to be used in rendered code
     *
     * @return string
     */
    public function renderCode($code, array $variables = array())
    {
        return $this->render($code, 'source.js', $variables);
    }

    /**
     * Add a variable or an array of variables to be shared with all templates that will be rendered
     * by the instance of Pug.
     *
     * @param array|string $variables|$key an associatives array of variable names and values, or a
     *                                     variable name if you wish to sahre only one
     * @param mixed        $value          if you pass an array as first argument, the second
     *                                     argument will be ignored, else it will used as the
     *                                     variable value for the variable name you passed as first
     *                                     argument
     */
    public function share($variables, $value = null)
    {
        if (!is_array($variables)) {
            $variables = array(strval($variables) => $value);
        }
        $this->sharedVariables = array_merge($this->sharedVariables, $variables);
    }

    /**
     * Remove all previously set shared variables.
     */
    public function resetSharedVariables()
    {
        $this->sharedVariables = array();
    }
}
