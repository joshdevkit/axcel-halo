<?php

namespace Axcel\AxcelCore\Migrations;

use DateTime;

class MigrationParser
{
    public static function generateMigration(string $name): string
    {
        $name = self::sanitizeTableName($name); // Remove "_table" if exists
        $timestamp = (new DateTime())->format('YmdHis');
        $className = "Version{$timestamp}_" . self::convertToClassName($name);
        $filename = "{$className}.php";
        $filePath = __DIR__ . "/../../../database/migrations/{$filename}";

        if (file_exists($filePath)) {
            echo "Migration already exists: database/migrations/{$filename}\n";
            return $filename;
        }

        // Pluralize the table name like Laravel (for migration file)
        $pluralizedTableName = self::pluralizeTableName($name);

        $stub = self::getMigrationStub($className, $pluralizedTableName);

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        file_put_contents($filePath, $stub);

        echo "Migration created: database/migrations/{$filename}\n";
        return $filename;
    }

    /**
     * Convert snake_case to PascalCase for class names.
     */
    private static function convertToClassName(string $name): string
    {
        return ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
    }

    /**
     * Remove "_table" from the migration name.
     */
    private static function sanitizeTableName(string $name): string
    {
        return preg_replace('/_table$/', '', strtolower($name));
    }

    /**
     * Pluralize table name in Laravel style
     */
    private static function pluralizeTableName(string $singular): string
    {
        // Handle some common irregular plurals
        $irregulars = [
            'child' => 'children',
            'person' => 'people',
            'man' => 'men',
            'woman' => 'women',
            'foot' => 'feet',
            'tooth' => 'teeth',
            'goose' => 'geese',
            'mouse' => 'mice',
        ];

        if (array_key_exists($singular, $irregulars)) {
            return $irregulars[$singular];
        }

        // Handle words ending with "y" (city→cities, but boy→boys)
        if (preg_match('/[^aeiou]y$/i', $singular)) {
            return preg_replace('/y$/i', 'ies', $singular);
        }

        // Handle words ending with "f" or "fe" (leaf→leaves, knife→knives)
        if (preg_match('/(?:([^f])f|fe)$/i', $singular)) {
            return preg_replace('/(?:([^f])f|fe)$/i', '$1ves', $singular);
        }

        // Handle words ending with "is" (analysis→analyses)
        if (preg_match('/is$/i', $singular)) {
            return preg_replace('/is$/i', 'es', $singular);
        }

        // Handle words ending with "us" (cactus→cacti)
        if (preg_match('/us$/i', $singular)) {
            return preg_replace('/us$/i', 'i', $singular);
        }

        // Handle words ending with "s", "sh", "ch", "x", or "z" (box→boxes, buzz→buzzes)
        if (preg_match('/(?:s|sh|ch|x|z)$/i', $singular)) {
            return $singular . 'es';
        }

        // Default: just add "s"
        return $singular . 's';
    }

    private static function getMigrationStub(string $className, string $tableName): string
    {
        return <<<PHP
<?php


namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Types\Types;

final class {$className} extends AbstractMigration
{
    public function up(Schema \$schema): void
    {
        \$table = \$schema->createTable('{$tableName}');
        
        \$table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setNotnull(true)
             ->setUnsigned(true);
        \$table->setPrimaryKey(['id']);


        \$table->addColumn('created_at', Types::DATETIME_MUTABLE)
            ->setNotnull(true)
            ->setDefault('CURRENT_TIMESTAMP');
        
        \$table->addColumn('updated_at', Types::DATETIME_MUTABLE)
            ->setNotnull(false)
            ->setDefault(null);
        
    }

    public function down(Schema \$schema): void
    {
        \$schema->dropTable('{$tableName}');
    }
}
PHP;
    }
}
