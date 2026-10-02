<?php

require_once __DIR__ . '/../classes/Db.php';

// Public reads go through the visible_news view, so posts scheduled for the future stay hidden.
class News
{
    public static function all(): array
    {
        return Db::connection()->query(
            'SELECT * FROM visible_news ORDER BY published_at DESC'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Db::connection()->prepare('SELECT * FROM visible_news WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }
}
