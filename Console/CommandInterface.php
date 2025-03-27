<?php

namespace Axcel\AxcelCore\Console;

interface CommandInterface
{
    public function execute(array $arguments): void;
}
