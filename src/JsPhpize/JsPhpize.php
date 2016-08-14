<?php

namespace JsPhpize;

use JsPhpize\Compiler\Compiler;
use JsPhpize\Compiler\Exception;
use JsPhpize\Parser\Parser;

class JsPhpize
{
    /**
     * @var string
     */
    protected $stream = 'jsphpize.stream';

    /**
     * @var bool
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

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function getOption($key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * @param  string $input file or content
     *
     * @return mixed
     */
    public function compile($input)
    {
        $parser = new Parser($this, $input);
        $compiler = new Compiler($this);

        return $compiler->compile($parser->parse());
    }

    /**
     * @param  string $input file or content
     *
     * @return mixed
     */
    public function compileWithoutDependencies($input)
    {
        $parser = new Parser($this, $input);
        $compiler = new Compiler($this);
        $block = $parser->parse();
        $this->dependencies = array_merge($this->dependencies, $block->popDependencies());

        return $compiler->compile($block);
    }

    /**
     * @return mixed
     */
    public function compileDependencies()
    {
        $parser = new Parser($this, '');
        $compiler = new Compiler($this);
        $block = $parser->parse();
        $block->addDependencies($this->dependencies);

        return $compiler->compile($block);
    }

    /**
     * @param  string $input file or content
     *
     * @return mixed
     */
    public function render($input)
    {
        if (!in_array($this->stream, $this->streamsRegistered)) {
            $this->streamsRegistered[] = $this->stream;
            if (in_array($this->stream, stream_get_wrappers())) {
                stream_wrapper_unregister($this->stream);
            }
            $classParts = explode('\\', get_class($this));
            stream_wrapper_register($this->stream, $classParts[0] . '\Stream\ExpressionStream');
        }

        try {
            return include $this->stream . '://data;<?php ' . $this->compile($input);
        } catch (\JsPhpize\Compiler\Exception $e) {
            throw $e;
        } catch (\JsPhpize\Lexer\Exception $e) {
            throw $e;
        } catch (\JsPhpize\Parser\Exception $e) {
            throw $e;
        } catch (\Exception $e) {
            $summary = $input;
            if (strlen($summary) > 50) {
                $summary = substr($summary, 0, 47) . '...';
            }
            throw new Exception("An error occur in [$summary]:\n" . $e->getMessage(), 2, E_ERROR, __FILE__, __LINE__, $e);
        }
    }
}
