<?php

namespace Axcel\AxcelCore\Exceptions;

class StaticMethodException extends \BadMethodCallException
{
    protected string $class;
    protected string $method;
    protected array $parameters;
    protected array $callerInfo;

    public function __construct(string $class, string $method, array $parameters = [], string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->parameters = $parameters;
        $this->callerInfo = $this->getCaller();
        // dd($this->getCaller());
        // dd($this->callerInfo);
        $detailedMessage = sprintf(
            "Static method '%s()' not found in class '%s'.\n" .
                "Called from: %s on line %s\n" .
                "%s",
            $method,
            $class,

            $this->callerInfo['file'],
            $this->callerInfo['line'],
            $this->formatParameters($parameters)
        );

        parent::__construct($message ?: $detailedMessage, $code, $previous);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getCallerInfo(): array
    {
        return $this->callerInfo;
    }

    protected function getCaller(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
        return $trace[2] ?? [];
    }

    protected function formatParameters(array $parameters): string
    {
        return implode(', ', array_map(function ($param) {
            if (is_object($param)) {
                return get_class($param);
            } elseif (is_array($param)) {
                return 'array[' . count($param) . ']';
            } else {
                return gettype($param) . '(' . (is_string($param) ? "'" . $param . "'" : $param) . ')';
            }
        }, $parameters));
    }
}
