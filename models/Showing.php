<?php

require_once __DIR__ . '/../classes/Db.php';

// All reads/writes for the showings table (a movie playing on a screen at a specific time).
class Showing
{
    public static function screens(): array
    {
        return Db::connection()->query('SELECT id, name FROM screens ORDER BY id')->fetchAll();
    }

    // Today's showings (one row per showing, not grouped) for the front page's "Today's Program" —
    // grouping by movie for display happens in PHP, same as schedule.php groups by screen.
    public static function today(): array
    {
        return Db::connection()->query(
            "SELECT s.id, s.show_datetime, m.id AS movie_id, m.title, m.duration_minutes, m.poster_url,
                    sc.name AS screen_name, sc.type AS screen_type,
                    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
             FROM showings s
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             LEFT JOIN movie_genres mg ON mg.movie_id = m.id
             LEFT JOIN genres g ON g.id = mg.genre_id
             WHERE DATE(s.show_datetime) = CURDATE()
             GROUP BY s.id, m.id, m.title, m.duration_minutes, m.poster_url, sc.name, sc.type
             ORDER BY s.show_datetime"
        )->fetchAll();
    }

    // One row per movie's earliest showing after today, for the front page's smaller "Later This
    // Week" teaser. The subquery pins the join to that specific earliest showing, so screen_type
    // reflects the showing actually being displayed (a movie could play on more than one screen).
    public static function laterThisWeekPreview(int $limit): array
    {
        $stmt = Db::connection()->prepare(
            "SELECT m.id AS movie_id, m.title, m.duration_minutes, m.poster_url,
                    sc.name AS screen_name, sc.type AS screen_type,
                    s.show_datetime AS next_show_datetime,
                    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
             FROM showings s
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             LEFT JOIN movie_genres mg ON mg.movie_id = m.id
             LEFT JOIN genres g ON g.id = mg.genre_id
             WHERE s.show_datetime >= CURDATE() + INTERVAL 1 DAY
               AND s.show_datetime = (
                   SELECT MIN(s2.show_datetime) FROM showings s2
                   WHERE s2.movie_id = m.id AND s2.show_datetime >= CURDATE() + INTERVAL 1 DAY
               )
             GROUP BY m.id, m.title, m.duration_minutes, m.poster_url, sc.name, sc.type, s.show_datetime
             ORDER BY s.show_datetime
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
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
