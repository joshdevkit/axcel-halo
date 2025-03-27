<?php

namespace Axcel\AxcelCore\Autloader;

use Axcel\Core\Bus\Container;

class Config
{
    private array $config = [];
    private string $configPath;

    public function __construct(Container $container)
    {
        $this->configPath = $container->make('configPath');

        $this->loadConfigs();
    }

    private function loadConfigs()
    {
        foreach (glob($this->configPath . '*.php') as $file) {
            $configKey = basename($file, '.php');
            $this->config[$configKey] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }

        return $value;
    }
}
