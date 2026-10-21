<?php

class User
{
    public static function all(): array
    {
        return Db::connection()->query(
            'SELECT name, email, role FROM users ORDER BY name'
        )->fetchAll();
    }

    public static function emailExists(string $email): bool
    {
        $stmt = Db::connection()->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->execute([$email]);

        return (bool) $stmt->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        $stmt = Db::connection()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Db::connection()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);

        return $stmt->fetch() ?: null;
    }

    // New accounts are always customers; the admin account is seeded, never self-registered.
    public static function create(string $name, string $email, string $password): void
    {
        Db::connection()->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'
        )->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    }
}
