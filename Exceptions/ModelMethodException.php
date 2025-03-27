<?php

namespace App\Core\Exceptions;

class ModelMethodException extends \BadMethodCallException
{
    protected string $model;
    protected string $method;
    protected array $arguments;
    protected array $callerInfo;

    public function __construct(string $model, string $method, array $arguments = [], string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->model = $model;
        $this->method = $method;
        $this->arguments = $arguments;
        $this->callerInfo = $this->getCaller();

        $detailedMessage = sprintf(
            "Method '%s' not found in model '%s'.\n" .
                "Called from: %s on line %s\n" .
                "Arguments provided: %s",
            $method,
            $model,
            $this->callerInfo['file'] ?? 'unknown',
            $this->callerInfo['line'] ?? 'unknown',
            $this->formatArguments($arguments)
        );

        parent::__construct($message ?: $detailedMessage, $code, $previous);
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getCallerInfo(): array
    {
        return $this->callerInfo;
    }

    protected function getCaller(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);

        // Skip the current method, the constructor, and the calling method
        // to get to the actual caller
        return $trace[3] ?? [];
    }

    protected function formatArguments(array $arguments): string
    {
        return implode(', ', array_map(function ($arg) {
            if (is_object($arg)) {
                return get_class($arg);
            } elseif (is_array($arg)) {
                return 'array[' . count($arg) . ']';
            } else {
                return gettype($arg) . '(' . (is_string($arg) ? "'" . $arg . "'" : $arg) . ')';
            }
        }, $arguments));
    }
}
