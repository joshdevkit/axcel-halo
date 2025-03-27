<?php

namespace Axcel\AxcelCore\Eloquent\Database;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = ConnectionManager::getPdo();
        }

        return self::$instance;
    }
}
