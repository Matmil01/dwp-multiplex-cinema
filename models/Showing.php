<?php

// All reads/writes for the showings table (a movie playing on a screen at a specific time).
class Showing
{
    public static function screens(): array
    {
        return Db::connection()->query(
            "SELECT id, type FROM screens ORDER BY FIELD(type, '16mm', '35mm', '70mm', 'imax')"
        )->fetchAll();
    }

    // Every screen (even ones with nothing scheduled) plus its upcoming showings, for the public
    // Screens page's full schedule — LEFT JOIN keeps empty screens in the result.
    public static function upcomingByScreen(): array
    {
        return Db::connection()->query(
            "SELECT sc.id AS screen_id, sc.type AS screen_type,
                    s.id AS showing_id, s.show_datetime, m.id AS movie_id, m.title AS movie_title
             FROM screens sc
             LEFT JOIN showings s ON s.screen_id = sc.id AND s.show_datetime >= NOW()
             LEFT JOIN movies m ON m.id = s.movie_id
               ORDER BY FIELD(sc.type, '16mm', '35mm', '70mm', 'imax'), s.show_datetime"
        )->fetchAll();
    }

    // Today's showings (one row per showing, not grouped) for the front page's "Today's Program" —
    // grouping by movie for display happens in PHP, same as schedule.php groups by screen.
    public static function today(): array
    {
        return Db::connection()->query(
            'SELECT * FROM today_showings ORDER BY show_datetime'
        )->fetchAll();
    }

    // One row per screen type's earliest showing after today, for the front page's "Later This Week" teaser.
    public static function laterThisWeekPreview(int $limit): array
    {
        $stmt = Db::connection()->prepare(
            "SELECT m.id AS movie_id, m.title, m.release_year, m.duration_minutes, m.poster_url,
                    sc.type AS screen_type,
                    s.show_datetime AS next_show_datetime,
                    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
             FROM showings s
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             LEFT JOIN movie_genres mg ON mg.movie_id = m.id
             LEFT JOIN genres g ON g.id = mg.genre_id
             WHERE s.show_datetime >= CURDATE() + INTERVAL 1 DAY
               AND s.id = (
                   SELECT s2.id
                   FROM showings s2
                   JOIN screens sc2 ON sc2.id = s2.screen_id
                   WHERE sc2.type = sc.type
                     AND s2.show_datetime >= CURDATE() + INTERVAL 1 DAY
                   ORDER BY s2.show_datetime, s2.id
                   LIMIT 1
                 )
             GROUP BY m.id, m.title, m.release_year, m.duration_minutes, m.poster_url, sc.type, s.show_datetime
             ORDER BY FIELD(sc.type, '16mm', '35mm', '70mm', 'imax')
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
            'SELECT s.id, s.screen_id, s.movie_id, s.show_datetime, m.title AS movie_title, m.duration_minutes
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

    // Upcoming showings for one movie, joined with the screen type, for the public movie page.
    public static function upcomingForMovie(int $movieId): array
    {
        $stmt = Db::connection()->prepare(
            'SELECT s.id, s.show_datetime, sc.type AS screen_type
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

        // Read the saved runtime so the form cannot supply a made-up movie length.
        $runtimeQuery = $db->prepare('SELECT duration_minutes FROM movies WHERE id = ?');
        $runtimeQuery->execute([$input['movie_id']]);
        // Missing, zero, or negative runtimes cannot be used to check whether showings overlap.
        if ((int) $runtimeQuery->fetchColumn() <= 0) {
            throw new RuntimeException('Set a runtime greater than zero for this movie in Movies before scheduling it.');
        }

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
