<?php

namespace App\Core\Console\Commands;

use Axcel\Core\Console\CommandInterface;
use Axcel\Core\Migrations\MigrationParser;

class MigrationCommand implements CommandInterface
{
    public function execute(array $arguments): void
    {
        if (empty($arguments)) {
            echo "❌ Usage: php dev create:migration <table_name>_table\n";
            exit(1);
        }

        $tableName = $arguments[0];

        if (!str_ends_with($tableName, '_table')) {
            echo "⚠️  Error: Table name must end with '_table'.\n";
            echo "✅ Example: php dev create:migration users_table\n";
            exit(1);
        }

        echo "🛠️  Generating migration for: {$tableName}\n";
        MigrationParser::generateMigration($tableName);
    }
}
