<?php

namespace App\Core\Exceptions;

class RouteDispatchException extends \RuntimeException
{
    private string $method;
    private array $action;

    public function __construct(string $method, array $action, string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        $this->method = $method;
        $this->action = $action;
        $detailedMessage = sprintf(
            "The method called (%s) is not define at %s ",
            $method,
            $action[0] ?? 'undefined',
            $action[0] ?? 'undefined'
        );

        parent::__construct($message ?: $detailedMessage, $code, $previous);
    }

    public function getRequestMethod(): string
    {
        return $this->method;
    }

    public function getAction(): array
    {
        return $this->action;
    }
}
