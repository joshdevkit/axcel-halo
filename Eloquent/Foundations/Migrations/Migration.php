<?php

namespace App\Core\Eloquent\Foundations\Migrations;

use App\Core\Eloquent\Foundations\Schema\Blueprint;

class Migration
{
    protected $blueprint;

    public function __construct()
    {
        $this->blueprint = new Blueprint();
    }

    // Create a table using a closure to define columns
    public function createTable($tableName, $callback)
    {
        // Initialize the closure, passing in the Blueprint instance
        $callback($this->blueprint);

        // After the callback runs, generate the table SQL
        $this->buildTable($tableName);
    }

    protected function buildTable($tableName)
    {
        $columns = $this->blueprint->getColumns();

        echo "Creating table: {$tableName}\n";
        foreach ($columns as $column) {
            $this->generateColumnSQL($column);
        }
    }

    protected function generateColumnSQL($column)
    {
        echo "Column: {$column['name']} Type: {$column['type']}\n";
        if (isset($column['attributes']['references'])) {
            echo "Foreign references: {$column['attributes']['references']}\n";
        }
    }
}
