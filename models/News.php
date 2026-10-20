<?php

// Public reads go through the visible_news view, so posts scheduled for the future stay hidden.
class News
{
    public $id;
    public $title;
    public $body;
    public $image_path;
    public $published_at;

    public function __construct(array $row)
    {
        $this->id = (int) $row['id'];
        $this->title = $row['title'];
        $this->body = $row['body'];
        $this->image_path = $row['image_path'];
        $this->published_at = $row['published_at'];
    }

    public function publishedLabel(): string
    {
        return date('F j, Y', strtotime($this->published_at));
    }

    public static function all(): array
    {
        return Db::connection()->query(
            'SELECT * FROM visible_news ORDER BY published_at DESC'
        )->fetchAll();
    }

    public static function find(int $id): ?News
    {
        $stmt = Db::connection()->prepare('SELECT * FROM visible_news WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? new self($row) : null;
    }

    // Admin reads the raw table (not the view), so drafts/future posts are visible for editing.
    public static function allForAdmin(): array
    {
        return Db::connection()->query(
            'SELECT * FROM news ORDER BY published_at DESC'
        )->fetchAll();
    }

    public static function findForAdmin(int $id): ?array
    {
        $stmt = Db::connection()->prepare('SELECT * FROM news WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    // Short teaser derived from the body, cut at a word boundary instead of mid-word.
    public static function excerpt(string $body, int $length = 150): string
    {
        $body = trim($body);
        if (strlen($body) <= $length) {
            return $body;
        }

        $truncated = substr($body, 0, $length);
        $lastSpace = strrpos($truncated, ' ');
        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }

        return $truncated . '…';
    }

    public static function save(array $input): int
    {
        $db = Db::connection();

        if (empty($input['id'])) {
            $stmt = $db->prepare(
                'INSERT INTO news (title, body, image_path, published_at) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $input['title'],
                $input['body'],
                $input['image_path'] ?: null,
                $input['published_at'],
            ]);

            return (int) $db->lastInsertId();
        }

        $db->prepare(
            'UPDATE news SET title = ?, body = ?, image_path = ?, published_at = ? WHERE id = ?'
        )->execute([
            $input['title'],
            $input['body'],
            $input['image_path'] ?: null,
            $input['published_at'],
            $input['id'],
        ]);

        return (int) $input['id'];
    }

    public static function delete(int $id): void
    {
        Db::connection()->prepare('DELETE FROM news WHERE id = ?')->execute([$id]);
    }
}
