<?php

namespace App\Core\Eloquent\Database;

use PDO;
use PDOException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use App\Core\Application;

class ConnectionManager
{
    private static array $instances = [];
    private static ?Connection $dbalConnection = null;

    public static function getPdo(string $name = 'default'): PDO
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $container = Application::getInstance();
        $config = $container->make('config');

        $defaultConnection = $config->get('database.default');
        $name = $name === 'default' ? $defaultConnection : $name;

        $connections = $config->get('database.connections', []);

        if (!isset($connections[$name])) {
            throw new \Exception("Database connection '{$name}' not found.");
        }

        $connection = $connections[$name];

        try {
            $pdo = new PDO(
                "{$connection['driver']}:host={$connection['host']};dbname={$connection['database']}",
                $connection['username'],
                $connection['password'],
                $connection['options']
            );

            self::$instances[$name] = $pdo;
            return $pdo;
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    public static function getDbalConnection(): Connection
    {
        if (self::$dbalConnection) {
            return self::$dbalConnection;
        }

        $container = Application::getInstance();
        $config = $container->make('config');

        $defaultConnection = $config->get('database.default');
        $connection = $config->get("database.connections.{$defaultConnection}");

        if (!$connection) {
            throw new \Exception("Database connection '{$defaultConnection}' not found.");
        }

        $connectionParams = [
            'dbname'   => $connection['database'],
            'user'     => $connection['username'],
            'password' => $connection['password'],
            'host'     => $connection['host'],
            'driver'   => 'pdo_mysql',
        ];

        self::$dbalConnection = DriverManager::getConnection($connectionParams);
        return self::$dbalConnection;
    }
}
