<?php

namespace Axcel\AxcelCore\Eloquent\Foundations\Schema;

use Axcel\AxcelCore\Eloquent\Foundations\Migrations\Migration;

class Schema
{
    // Create a new table
    public static function create(string $tableName, \Closure $callback)
    {
        $migration = new Migration();

        // Use the migration object to handle table creation
        $migration->createTable($tableName, $callback);
    }

    // Drop a table if it exists
    public static function dropIfExists(string $tableName)
    {
        // Perform the drop logic, just as an example
        echo "Dropping table: {$tableName}\n";
    }
}
