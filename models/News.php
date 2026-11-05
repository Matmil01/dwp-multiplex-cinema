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

    // "2026-10-31" -> "October 31, 2026" for display.
    public function publishedLabel(): string
    {
        return date('F j, Y', strtotime($this->published_at));
    }

    // Public list page. DESC = newest first.
    public static function all(): array
    {
        return Db::connection()->query(
            'SELECT * FROM visible_news ORDER BY published_at DESC'
        )->fetchAll();
    }

    // Public article page. Because this reads the view, a future article gives null (-> 404)
    // even though its row exists in the news table.
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
    // `int $length = 150` = $length is optional and is 150 unless the caller says otherwise.
    public static function excerpt(string $body, int $length = 150): string
    {
        $body = trim($body);
        // Short enough already: use it as is.
        if (strlen($body) <= $length) {
            return $body;
        }

        // Cut at 150 characters, then back up to the last space so a word isn't chopped in half.
        // substr(text, start, length) takes a slice; strrpos() finds the LAST space in it.
        $truncated = substr($body, 0, $length);
        $lastSpace = strrpos($truncated, ' ');
        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }

        return $truncated . '…';
    }

    // Called by the admin news editor. image_path has already been handled by the controller
    // (file saved to disk, or kept/removed); this just stores the path text.
    public static function save(array $input): int
    {
        $db = Db::connection();

        // No id in the form = new article: INSERT. Otherwise UPDATE the existing row.
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

    // Only the row is deleted here; the controller removes the image file from disk itself.
    public static function delete(int $id): void
    {
        Db::connection()->prepare('DELETE FROM news WHERE id = ?')->execute([$id]);
    }
}
