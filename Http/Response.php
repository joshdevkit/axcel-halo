<?php

namespace App\Core\Http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    public static function json(array $data, int $status = 200): self
    {
        return new self(json_encode($data), $status, ['Content-Type' => 'application/json']);
    }

    public static function redirect(): Redirect
    {
        return new Redirect();
    }
}
