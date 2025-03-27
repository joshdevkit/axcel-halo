<?php

namespace Axcel\AxcelCore\Eloquent\Foundations\Schema;

class Blueprint
{
    protected $columns = [];

    // Method for adding a column definition
    public function addColumn($type, $name, $attributes = [])
    {
        $this->columns[] = [
            'name' => $name,
            'type' => $type,
            'attributes' => $attributes
        ];
    }

    // Adding ID column (bigint, unsigned, primary key)
    public function id($name = 'id')
    {
        $this->addColumn('unsignedBigInteger', $name, ['primary' => true]);
        return $this;
    }

    // Adding a string column
    public function string($name, $length = 255)
    {
        $this->addColumn('string', $name, ['length' => $length]);
        return $this;
    }

    // Adding timestamp column with current timestamp as default
    public function timestamp($name, $nullable = false)
    {
        $attributes = $nullable ? ['default' => 'CURRENT_TIMESTAMP', 'nullable' => true] : ['default' => 'CURRENT_TIMESTAMP'];
        $this->addColumn('timestamp', $name, $attributes);
        return $this;
    }

    // Adding nullable column (without requiring arguments)
    public function nullable()
    {
        foreach ($this->columns as &$column) {
            $column['attributes']['nullable'] = true;
        }
        return $this;
    }


    // Adding unsignedBigInteger column
    public function unsignedBigInteger($name)
    {
        $this->addColumn('unsignedBigInteger', $name);
        return $this;
    }

    // Adding foreign key reference
    public function foreignId($name, $referencedTable)
    {
        $this->addColumn('foreignId', $name, ['references' => $referencedTable]);
        return $this;
    }

    // Adding text column
    public function text($name)
    {
        $this->addColumn('text', $name);
        return $this;
    }

    // Adding longText column
    public function longText($name)
    {
        $this->addColumn('longText', $name);
        return $this;
    }

    // Adding unique constraint to column
    public function unique()
    {
        foreach ($this->columns as &$column) {
            $column['attributes']['unique'] = true;
        }
        return $this;
    }


    // Adding index to column
    public function index($name)
    {
        $this->addColumn('index', $name);
        return $this;
    }

    // Add timestamps columns ('created_at' and 'updated_at')
    public function timestamps()
    {
        $this->timestamp('created_at');
        $this->timestamp('updated_at', true); // Typically nullable for updated_at
        return $this;
    }

    // Get all columns
    public function getColumns()
    {
        return $this->columns;
    }
}
