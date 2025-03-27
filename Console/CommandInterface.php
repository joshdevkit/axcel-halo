<?php

namespace App\Core\Console;

interface CommandInterface
{
    public function execute(array $arguments): void;
}
