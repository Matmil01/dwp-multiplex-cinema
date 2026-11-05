<?php

class User
{
    // For the admin Users page. Only the columns it shows — never the password hashes.
    public static function all(): array
    {
        return Db::connection()->query(
            'SELECT name, email, role FROM users ORDER BY name'
        )->fetchAll();
    }

    // "Is this email already taken?" SELECT 1 just asks for a row to exist, nothing more;
    // (bool) turns "got a row" / "got false" into true / false.
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
    // (role is left out of the INSERT, so the column's default 'customer' from schema.sql applies.)
    // password_hash() scrambles the password one-way with a random "salt" mixed in, so two
    // users with the same password still get different hashes. PASSWORD_DEFAULT = let PHP
    // pick its current recommended algorithm (bcrypt today), which may improve over time.
    public static function create(string $name, string $email, string $password): void
    {
        Db::connection()->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'
        )->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    }

    // Called after each wrong password. Adds 1 to the counter and, if that makes it 5 or more,
    // sets locked_until to 15 minutes from now. IF(condition, value_if_true, value_if_false)
    // is the database's one-line if. Both updates happen in ONE statement so there is no
    // moment where the counter is raised but the lock not yet set.
    // locked_until is listed first on purpose: MariaDB applies SET left to right, so it must read the count before it is raised.
    public static function recordFailedLogin(int $id): void
    {
        Db::connection()->prepare(
            'UPDATE users
             SET locked_until = IF(failed_logins + 1 >= 5, NOW() + INTERVAL 15 MINUTE, locked_until),
                 failed_logins = failed_logins + 1
             WHERE id = ?'
        )->execute([$id]);
    }

    // Called after a correct password: a successful login wipes the slate clean.
    public static function clearFailedLogins(int $id): void
    {
        Db::connection()->prepare(
            'UPDATE users SET failed_logins = 0, locked_until = NULL WHERE id = ?'
        )->execute([$id]);
    }
}
