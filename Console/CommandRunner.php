<?php

namespace Axcel\AxcelCore\Console;

use Axcel\Core\Console\Commands\MigrateCommand;
use Axcel\Core\Console\Commands\MigrationCommand;

class CommandRunner
{
    private array $commands = [
        'create:migration' => MigrationCommand::class,
        'migrate'          => MigrateCommand::class,
    ];

    private array $arguments;

    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    public function run(): void
    {
        if (count($this->arguments) < 2) {
            echo "Usage: php dev <command> [arguments]\n";
            exit(1);
        }

        $commandName = $this->arguments[1];
        $commandArgs = array_slice($this->arguments, 2);

        echo "Executing command: {$commandName}\n"; // Debugging output

        if (!isset($this->commands[$commandName])) {
            echo "Unknown command: $commandName\n";
            exit(1);
        }

        $commandClass = $this->commands[$commandName];
        echo "Running: {$commandClass}\n"; // Debugging output

        $commandInstance = new $commandClass();
        $commandInstance->execute($commandArgs);
    }
}
