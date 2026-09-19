<?php

// Single shared PDO connection, lazily created on first use.
class Db
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $db = (require __DIR__ . '/../config/config.php')['db'];
            $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";

            self::$connection = new PDO($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return self::$connection;
    }
}
