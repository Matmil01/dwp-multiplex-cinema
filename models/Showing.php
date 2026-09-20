<?php

require_once __DIR__ . '/../classes/Db.php';

// All reads/writes for the showings table (a movie playing on a screen at a specific time).
class Showing
{
    public static function screens(): array
    {
        return Db::connection()->query('SELECT id, name FROM screens ORDER BY id')->fetchAll();
    }

    // One day's showings, joined with the movie title/duration for the admin schedule grid.
    public static function forDate(string $date): array
    {
        $stmt = Db::connection()->prepare(
            'SELECT s.id, s.screen_id, s.show_datetime, m.title AS movie_title, m.duration_minutes
             FROM showings s
             JOIN movies m ON m.id = s.movie_id
             WHERE DATE(s.show_datetime) = ?
             ORDER BY s.show_datetime'
        );
        $stmt->execute([$date]);

        return $stmt->fetchAll();
    }

    // A single showing plus its movie's title, shaped for pre-filling the admin editor form.
    public static function find(int $id): ?array
    {
        $stmt = Db::connection()->prepare(
            'SELECT s.*, m.title AS movie_title FROM showings s JOIN movies m ON m.id = s.movie_id WHERE s.id = ?'
        );
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    // Upcoming showings for one movie, joined with the screen name, for the public movie page.
    public static function upcomingForMovie(int $movieId): array
    {
        $stmt = Db::connection()->prepare(
            'SELECT s.id, s.show_datetime, sc.name AS screen_name
             FROM showings s
             JOIN screens sc ON sc.id = s.screen_id
             WHERE s.movie_id = ? AND s.show_datetime >= NOW()
             ORDER BY s.show_datetime'
        );
        $stmt->execute([$movieId]);

        return $stmt->fetchAll();
    }

    // Overlap prevention lives in a DB trigger (see schema.sql) — this just surfaces that
    // trigger's error as a normal exception for the admin form to display.
    public static function save(array $input): int
    {
        $db = Db::connection();
        $params = [$input['movie_id'], $input['screen_id'], $input['show_datetime']];

        try {
            if (empty($input['id'])) {
                $db->prepare(
                    'INSERT INTO showings (movie_id, screen_id, show_datetime) VALUES (?, ?, ?)'
                )->execute($params);

                return (int) $db->lastInsertId();
            }

            $db->prepare(
                'UPDATE showings SET movie_id = ?, screen_id = ?, show_datetime = ? WHERE id = ?'
            )->execute([...$params, $input['id']]);

            return (int) $input['id'];
        } catch (PDOException $e) {
            if ($e->getCode() === '45000') {
                // Turn database's overlap error into a message the admin form can show.
                throw new RuntimeException($e->errorInfo[2] ?? $e->getMessage());
            }

            throw $e;
        }
    }

    public static function delete(int $id): void
    {
        Db::connection()->prepare('DELETE FROM showings WHERE id = ?')->execute([$id]);
    }
}
