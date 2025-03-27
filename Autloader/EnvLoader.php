<?php

namespace App\Core\Autloader;

use Dotenv\Dotenv;

class EnvLoader
{
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(base_path());
        $dotenv->load();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }
}
