<?php

require_once __DIR__ . '/../classes/Db.php';

// All reads/writes for the movies table and its genres/credits.
class Movie
{
    // Plain catalog list, no schedule info attached — used by the admin catalog and the schedule
    // page's movie picker. See allWithStatus() for the version the public site uses.
    public static function all(): array
    {
        $stmt = Db::connection()->query(
            'SELECT id, title, duration_minutes, release_year, poster_url FROM movies ORDER BY title'
        );
        $movies = $stmt->fetchAll();

        foreach ($movies as &$movie) {
            $movie['genres'] = self::genresFor((int) $movie['id']);
        }

        return $movies;
    }

    // Status is derived from showings, not stored: has an upcoming showing (now_showing), has only
    // past showings (previously_shown), or has none at all (not_scheduled).
    public static function allWithStatus(): array
    {
        $stmt = Db::connection()->query(
            'SELECT m.id, m.title, m.duration_minutes, m.release_year, m.poster_url,
                    MAX(CASE WHEN s.show_datetime >= NOW() THEN 1 ELSE 0 END) AS has_upcoming,
                    MAX(CASE WHEN s.show_datetime < NOW() THEN 1 ELSE 0 END) AS has_past
             FROM movies m
             LEFT JOIN showings s ON s.movie_id = m.id
             GROUP BY m.id
             ORDER BY m.title'
        );
        $movies = $stmt->fetchAll();

        foreach ($movies as &$movie) {
            $movie['genres'] = self::genresFor((int) $movie['id']);
            $movie['status'] = $movie['has_upcoming']
                ? 'now_showing'
                : ($movie['has_past'] ? 'previously_shown' : 'not_scheduled');
        }

        return $movies;
    }

    // Stops the same TMDB movie from being imported twice if it's searched and saved again.
    public static function existsByTmdbId(int $tmdbId): ?int
    {
        $stmt = Db::connection()->prepare('SELECT id FROM movies WHERE tmdb_id = ?');
        $stmt->execute([$tmdbId]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    // Full record + genres/credits, shaped for pre-filling the editor form.
    public static function find(int $id): ?array
    {
        $stmt = Db::connection()->prepare('SELECT * FROM movies WHERE id = ?');
        $stmt->execute([$id]);
        $movie = $stmt->fetch();

        if (!$movie) {
            return null;
        }

        $movie['genres'] = self::genresFor($id);
        $movie += self::creditsFor($id);

        return $movie;
    }

    // Upsert: updates an existing row (by id, or by tmdb_id if already imported), otherwise inserts.
    // Genres/credits are fully replaced by whatever the editor submitted, rather than diffed.
    public static function save(array $input): int
    {
        $db = Db::connection();

        $movieId = $input['id'] ?? null;
        if ($movieId === null && !empty($input['tmdb_id'])) {
            $movieId = self::existsByTmdbId((int) $input['tmdb_id']);
        }

        if ($movieId === null) {
            $stmt = $db->prepare(
                'INSERT INTO movies (tmdb_id, title, description, duration_minutes, poster_url, release_year)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $input['tmdb_id'] ?: null,
                $input['title'],
                $input['description'],
                $input['duration_minutes'] ?: null,
                $input['poster_url'] ?: null,
                $input['release_year'] ?: null,
            ]);
            $movieId = (int) $db->lastInsertId();
        } else {
            $stmt = $db->prepare(
                'UPDATE movies SET title = ?, description = ?, duration_minutes = ?, poster_url = ?, release_year = ? WHERE id = ?'
            );
            $stmt->execute([
                $input['title'],
                $input['description'],
                $input['duration_minutes'] ?: null,
                $input['poster_url'] ?: null,
                $input['release_year'] ?: null,
                $movieId,
            ]);
        }

        $db->prepare('DELETE FROM movie_genres WHERE movie_id = ?')->execute([$movieId]);
        foreach (self::splitList($input['genres'] ?? '') as $genreName) {
            $genreId = self::findOrCreateByName('genres', $genreName);
            $db->prepare('INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES (?, ?)')
                ->execute([$movieId, $genreId]);
        }

        $db->prepare('DELETE FROM movie_credits WHERE movie_id = ?')->execute([$movieId]);

        $director = trim($input['director'] ?? '');
        if ($director !== '') {
            self::attachCreditByName($movieId, $director, 'director', null);
        }

        foreach (self::splitList($input['writers'] ?? '') as $writer) {
            self::attachCreditByName($movieId, $writer, 'writer', null);
        }

        foreach (self::splitList($input['cast'] ?? '') as $index => $castName) {
            self::attachCreditByName($movieId, $castName, 'cast', $index + 1);
        }

        return $movieId;
    }

    public static function delete(int $id): void
    {
        Db::connection()->prepare('DELETE FROM movies WHERE id = ?')->execute([$id]);
    }

    private static function genresFor(int $movieId): array
    {
        $stmt = Db::connection()->prepare(
            'SELECT g.name FROM genres g
             JOIN movie_genres mg ON mg.genre_id = g.id
             WHERE mg.movie_id = ?'
        );
        $stmt->execute([$movieId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // director is a single name (or null), writers/cast are name lists (cast ordered by billing_order)
    private static function creditsFor(int $movieId): array
    {
        $stmt = Db::connection()->prepare(
            'SELECT p.name, mc.role FROM movie_credits mc
             JOIN people p ON p.id = mc.person_id
             WHERE mc.movie_id = ?
             ORDER BY mc.billing_order IS NULL, mc.billing_order'
        );
        $stmt->execute([$movieId]);

        $director = null;
        $writers = [];
        $cast = [];

        foreach ($stmt->fetchAll() as $row) {
            match ($row['role']) {
                'director' => $director = $row['name'],
                'writer' => $writers[] = $row['name'],
                'cast' => $cast[] = $row['name'],
            };
        }

        return ['director' => $director, 'writers' => $writers, 'cast' => $cast];
    }

    // Looks up a row by name in a lookup table (genres, people), creating it if it doesn't exist yet.
    private static function findOrCreateByName(string $table, string $name): int
    {
        $db = Db::connection();

        $stmt = $db->prepare("SELECT id FROM {$table} WHERE name = ?");
        $stmt->execute([$name]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        $db->prepare("INSERT INTO {$table} (name) VALUES (?)")->execute([$name]);

        return (int) $db->lastInsertId();
    }

    private static function attachCreditByName(int $movieId, string $name, string $role, ?int $billingOrder): void
    {
        // Editor fields are plain text, so people are matched/deduped by name here (not TMDB person id).
        $personId = self::findOrCreateByName('people', $name);

        Db::connection()->prepare(
            'INSERT INTO movie_credits (movie_id, person_id, role, billing_order) VALUES (?, ?, ?, ?)'
        )->execute([$movieId, $personId, $role, $billingOrder]);
    }

    private static function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), fn($v) => $v !== ''));
    }
}
