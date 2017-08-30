<?php

namespace JsPhpize\Nodes;

class FunctionCall extends Value
{
    /**
     * @var Value
     */
    protected $function;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var array
     */
    protected $children;

    /**
     * @var null|string
     */
    protected $applicant;

    public function __construct(Value $function, array $arguments, array $children, $applicant = null)
    {
        $this->function = $function;
        $this->arguments = $arguments;
        $this->applicant = $applicant;
        $this->children = $children;
    }

    public function getReadVariables()
    {
        $variables = $this->function->getReadVariables();
        foreach ($this->arguments as $argument) {
            $variables = array_merge($variables, $argument->getReadVariables());
        }

        return $variables;
    }
}
