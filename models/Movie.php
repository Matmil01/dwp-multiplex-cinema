<?php

// All reads/writes for the movies table and its genres/credits.
class Movie
{
    // The pieces of information one Movie object holds. They start empty and are filled
    // in by __construct below. "public" means other files are allowed to read them.
    public $id;
    public $tmdb_id;
    public $title;
    public $description;
    public $release_year;
    public $duration_minutes;
    public $poster_url;
    public $genres;
    public $director;
    public $writers;
    public $cast;

    // __construct runs automatically whenever code writes `new Movie($row)`.
    // $row is one database row (column name => value). Each line copies one value from
    // the row onto this object. `$this` means "the object currently being built".
    // (int) forces the value to be a whole number, because the database hands everything
    // over as text.
    public function __construct(array $row)
    {
        $this->id = (int) $row['id'];
        $this->tmdb_id = $row['tmdb_id'];
        $this->title = $row['title'];
        $this->description = $row['description'];
        $this->release_year = $row['release_year'];
        $this->duration_minutes = $row['duration_minutes'];
        $this->poster_url = $row['poster_url'];
        $this->genres = $row['genres'];
        $this->director = $row['director'];
        $this->writers = $row['writers'];
        $this->cast = $row['cast'];
    }

    // Builds the small grey line under a movie title, e.g. "1999 · Drama, Sci-Fi · 136 min".
    // It puts the three pieces in a list, array_filter() removes any that are empty (a
    // movie with no year just shows "Drama · 136 min"), and implode() glues the rest
    // together with " · " between them. `: string` means this function always gives
    // back a piece of text.
    public function metadataLine(): string
    {
        $meta = array_filter([
            $this->release_year,
            implode(', ', $this->genres),
            $this->duration_minutes ? $this->duration_minutes . ' min' : null,
        ]);

        return implode(' · ', $meta);
    }

    // Plain catalog list, no schedule info attached — used by the admin catalog and the schedule
    // page's movie picker. See allWithStatus() for the version the public site uses.
    // "static" means you call this as Movie::all() without making a Movie object first.
    public static function all(): array
    {
        // Db::connection() is the open line to the database (set up once in classes/Db.php
        // and reused everywhere). ->query() sends the SQL and gets the result back.
        // Using query() instead of the safer prepare() is OK here only because this SQL is
        // fixed text with nothing typed by a user in it.
        $stmt = Db::connection()->query(
            'SELECT id, title, duration_minutes, release_year, poster_url FROM movies ORDER BY title'
        );
        // fetchAll() turns the result into a list, one entry per movie row.
        $movies = $stmt->fetchAll();

        // Go through every movie and add its genres. The & in front of $movie is important:
        // without it PHP would hand us a copy of each row, we'd add genres to the copy, and
        // the real list would stay unchanged. With & we edit the real row in $movies.
        foreach ($movies as &$movie) {
            $movie['genres'] = self::genresFor((int) $movie['id']);
        }

        return $movies;
    }

    // Status is derived from showings, not stored: has an upcoming showing (now_showing), has only
    // past showings (previously_shown), or has none at all (not_scheduled).
    // Used by the public Movies page and its "Now Showing / Not Yet Scheduled / Previously
    // Shown" filter tabs.
    public static function allWithStatus(): array
    {
        // This one SQL statement looks at every movie together with all its showings and
        // works out two yes/no answers per movie. Reading it piece by piece:
        //   FROM movies m            start from the movies table ("m" is just a short nickname)
        //   LEFT JOIN showings s     attach each movie's showings. "LEFT" means: keep the
        //                            movie even if it has no showings at all.
        //   CASE WHEN ... THEN 1     for each showing, give a 1 if it is in the future, else 0
        //   MAX(...)                 across all of a movie's showings, keep the biggest value,
        //                            so the answer is 1 if ANY showing is in the future
        //   GROUP BY m.id            squash the many showing rows back into one row per movie
        //   NOW()                    the database's current date and time
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
            // `A ? B : C` is a one-line if: "if A is true use B, otherwise use C".
            // So: has a future showing -> now_showing; otherwise has a past showing ->
            // previously_shown; otherwise -> not_scheduled.
            $movie['status'] = $movie['has_upcoming']
                ? 'now_showing'
                : ($movie['has_past'] ? 'previously_shown' : 'not_scheduled');
        }

        return $movies;
    }

    // Stops the same TMDB movie from being imported twice if it's searched and saved again.
    // (TMDB = The Movie Database, the online service the admin page fetches movie info from.)
    // `: ?int` means this gives back a whole number, or null (= "nothing") if not found.
    public static function existsByTmdbId(int $tmdbId): ?int
    {
        // This is the safe two-step pattern described at the top of the file:
        // prepare() sends the SQL with a ? gap in it, execute() fills the gap with the value.
        $stmt = Db::connection()->prepare('SELECT id FROM movies WHERE tmdb_id = ?');
        $stmt->execute([$tmdbId]);
        // fetchColumn() gives back just one single value (here: the id), or false if no
        // row matched.
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    // Loads ONE movie with everything about it (genres, director, writers, cast).
    // Used by the movie details page and by the admin editor when pre-filling the form.
    // `: ?self` = gives back a Movie object, or null if there is no movie with that id.
    public static function find(int $id): ?self
    {
        // SELECT * = all columns. fetch() (no "All") gets just the first row.
        $stmt = Db::connection()->prepare('SELECT * FROM movies WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        // No row came back: the id doesn't exist. The controller turns this into a 404 page.
        if (!$row) {
            return null;
        }

        $row['genres'] = self::genresFor($id);
        // creditsFor() gives back a small list with director/writers/cast in it;
        // += adds those three entries onto $row.
        $row += self::creditsFor($id);

        // `self` means "this class", so this is `new Movie($row)` — the row is turned into
        // a Movie object using __construct at the top of the file.
        return new self($row);
    }

    // Called when the admin presses Save in the movie editor. $input is the submitted form.
    // One function handles both "create new" and "edit existing": if the movie already exists
    // (we know its id, or it was imported from TMDB before) it is updated, otherwise inserted.
    // Genres/credits are fully replaced by whatever the editor submitted, rather than diffed.
    public static function save(array $input): int
    {
        $db = Db::connection();
        // Saving a movie means several separate writes: the movie row, then its genres, then
        // its credits. beginTransaction() tells the database "treat everything until I say
        // commit() as one unit". If anything fails halfway, rollBack() undoes all of it, so
        // we never end up with a movie that has only half its genres saved.
        $db->beginTransaction();

        // try { ... } catch { ... }: run the code in try; if anything inside throws an
        // error, jump straight to the catch block at the bottom instead of crashing.
        try {
            // `$a ?? null` = "use $a if it exists, otherwise null". Existing movies send
            // their id with the form; new ones don't.
            $movieId = $input['id'] ?? null;
            if ($movieId === null && !empty($input['tmdb_id'])) {
                $movieId = self::existsByTmdbId((int) $input['tmdb_id']);
            }

            // Still no id after both checks = this is a brand new movie: INSERT a row.
            if ($movieId === null) {
                $stmt = $db->prepare(
                    'INSERT INTO movies (tmdb_id, title, description, duration_minutes, poster_url, release_year)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                // The six values below fill the six ? gaps, in the same order.
                // `$x ?: null` means "use $x, but if the field was left blank, store NULL
                // (= no value) instead of an empty string".
                $stmt->execute([
                    $input['tmdb_id'] ?: null,
                    $input['title'],
                    $input['description'],
                    $input['duration_minutes'] ?: null,
                    $input['poster_url'] ?: null,
                    $input['release_year'] ?: null,
                ]);
                // The database picks the id number for new rows itself; this asks what it chose.
                $movieId = (int) $db->lastInsertId();
            } else {
                // The movie already exists: UPDATE its row instead.
                // Changing a movie's length matters for the schedule: a longer film could now
                // overlap the next showing in the same room. So first check whether the length
                // actually changed; other edits (title, poster...) don't need schedule checks.
                $runtimeQuery = $db->prepare('SELECT duration_minutes FROM movies WHERE id = ?');
                $runtimeQuery->execute([$movieId]);
                $runtimeChanged = (int) $runtimeQuery->fetchColumn() !== (int) $input['duration_minutes'];

                // A length of 0 would make the overlap check meaningless, so refuse it for a
                // movie that already has showings. `throw` stops here and jumps to the catch.
                if ($runtimeChanged && (int) $input['duration_minutes'] <= 0) {
                    $showingQuery = $db->prepare('SELECT id FROM showings WHERE movie_id = ? LIMIT 1');
                    $showingQuery->execute([$movieId]);
                    // A movie already on the schedule must keep a positive runtime for overlap checks.
                    if ($showingQuery->fetchColumn() !== false) {
                        throw new RuntimeException('This movie has showings. Keep its runtime greater than zero.');
                    }
                }

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

                if ($runtimeChanged && (int) $input['duration_minutes'] > 0) {
                    // The database has a "trigger" (database/triggers.sql): a rule that runs
                    // automatically every time a showing is changed, and refuses the change if
                    // it would overlap another showing in the same room. This line "touches"
                    // each showing of this movie without really changing it (sets movie_id to
                    // what it already is), purely to make that rule run again with the new
                    // length. If the rule refuses, we land in the catch below and nothing is saved.
                    $db->prepare('UPDATE showings SET movie_id = movie_id WHERE movie_id = ?')
                        ->execute([$movieId]);
                }
            }

            // A movie can have many genres, and a genre belongs to many movies. The database
            // handles this with a small "link" table, movie_genres, where each row just says
            // "movie 5 has genre 3". Easiest way to update it: delete all of this movie's
            // links, then add one link per genre typed into the form.
            $db->prepare('DELETE FROM movie_genres WHERE movie_id = ?')->execute([$movieId]);
            foreach (self::splitList($input['genres'] ?? '') as $genreName) {
                $genreId = self::findOrCreateByName('genres', $genreName);
                // INSERT IGNORE = insert, but silently skip if this exact link already exists
                // (e.g. the admin typed the same genre twice).
                $db->prepare('INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES (?, ?)')
                    ->execute([$movieId, $genreId]);
            }

            // Same idea for people: movie_credits links a movie to a person plus their role
            // (director / writer / cast). Wipe and re-add.
            $db->prepare('DELETE FROM movie_credits WHERE movie_id = ?')->execute([$movieId]);

            // trim() removes spaces from the start and end of the text.
            $director = trim($input['director'] ?? '');
            if ($director !== '') {
                self::attachCreditByName($movieId, $director, 'director', null);
            }

            foreach (self::splitList($input['writers'] ?? '') as $writer) {
                self::attachCreditByName($movieId, $writer, 'writer', null);
            }

            // Cast order matters (top billing first), so each name also gets its position
            // number. $index counts from 0, so + 1 makes the first actor number 1.
            foreach (self::splitList($input['cast'] ?? '') as $index => $castName) {
                self::attachCreditByName($movieId, $castName, 'cast', $index + 1);
            }

            // Everything worked: make all the writes above permanent.
            $db->commit();
            return $movieId;
        } catch (Throwable $e) {
            // Something above failed (a PHP error or a database refusal). Undo every write
            // made since beginTransaction(), so the database looks exactly like before.
            $db->rollBack();
            // 45000 is the error number our overlap trigger uses when it refuses a change.
            // Swap the raw database error for a sentence the admin can understand.
            if ($e instanceof PDOException && $e->getCode() === '45000') {
                throw new RuntimeException('This runtime would make existing showings overlap. Adjust the schedule before changing it.');
            }
            // Any other error: pass it on unchanged so the generic error page shows.
            throw $e;
        }
    }

    // `: void` = this function gives nothing back; it just does its job.
    public static function delete(int $id): void
    {
        // Only one DELETE is needed. In database/schema.sql the genres, credits and showings
        // tables are set up with "ON DELETE CASCADE", which means: when a movie is deleted,
        // automatically delete the rows in these tables that belong to it too.
        // Bookings are deliberately NOT set up that way, so if a customer has booked a
        // showing of this movie, the database refuses the delete and we tell the admin why.
        try {
            Db::connection()->prepare('DELETE FROM movies WHERE id = ?')->execute([$id]);
        } catch (PDOException $e) {
            // 1451 is the database's error number for "can't delete, something still points
            // at this row" — here that means bookings exist for one of the movie's showings.
            if (($e->errorInfo[1] ?? 0) === 1451) {
                throw new RuntimeException('This movie has showings with bookings and can\'t be deleted.');
            }

            throw $e;
        }
    }

    // Gets the genre names for one movie, e.g. ['Drama', 'Sci-Fi'].
    // "private" = a helper that only the code in this file may call.
    private static function genresFor(int $movieId): array
    {
        // The movie_genres link table only stores numbers (movie id + genre id). JOIN
        // connects it to the genres table so we get the actual names.
        // "g" and "mg" are short nicknames for the two tables, used in the rest of the SQL.
        $stmt = Db::connection()->prepare(
            'SELECT g.name FROM genres g
             JOIN movie_genres mg ON mg.genre_id = g.id
             WHERE mg.movie_id = ?'
        );
        $stmt->execute([$movieId]);

        // Normally each row comes back as ['name' => 'Drama']. FETCH_COLUMN says "just give
        // me the values", so we get a simple list: ['Drama', 'Sci-Fi'].
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Gets the people attached to one movie, sorted into director / writers / cast.
    // director is a single name (or null), writers/cast are name lists (cast ordered by billing_order)
    private static function creditsFor(int $movieId): array
    {
        // ORDER BY ... IS NULL, ... = put people WITH a billing_order first (lowest number
        // first), and anyone without one (director, writers) after them.
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

        // Look at each person's role and put their name in the right place.
        // `match` = "compare this value against each option on the left, run the one that
        // fits". `$writers[] = ...` means "add to the end of the writers list".
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
    // Gives back the id for a genre or person name. If the name isn't in the table yet,
    // it is added first. This is why an admin can just type "Western" and it works.
    private static function findOrCreateByName(string $table, string $name): int
    {
        $db = Db::connection();

        // Here the table name IS written straight into the SQL text ({$table}), which goes
        // against the safety rule at the top of the file. It is acceptable only because
        // the two places that call this function pass the fixed words 'genres' or 'people'
        // — the table name never comes from a user. The name typed by the user ($name)
        // still goes through a ? gap like everywhere else.
        $stmt = $db->prepare("SELECT id FROM {$table} WHERE name = ?");
        $stmt->execute([$name]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        $db->prepare("INSERT INTO {$table} (name) VALUES (?)")->execute([$name]);

        return (int) $db->lastInsertId();
    }

    // Links one person to one movie with a role. `?int` on $billingOrder = a number or null
    // (directors and writers don't have a position number, only cast does).
    private static function attachCreditByName(int $movieId, string $name, string $role, ?int $billingOrder): void
    {
        // Editor fields are plain text, so people are matched/deduped by name here (not TMDB person id).
        $personId = self::findOrCreateByName('people', $name);

        Db::connection()->prepare(
            'INSERT INTO movie_credits (movie_id, person_id, role, billing_order) VALUES (?, ?, ?, ?)'
        )->execute([$movieId, $personId, $role, $billingOrder]);
    }

    // Turns the text an admin typed, like "Drama, Sci-Fi, ", into a clean list ['Drama', 'Sci-Fi'].
    // The line below is four steps nested inside each other; read it from the inside out:
    //   1. explode(',', $value)        cut the text at every comma -> ['Drama', ' Sci-Fi', ' ']
    //   2. array_map('trim', ...)      remove spaces around each piece -> ['Drama', 'Sci-Fi', '']
    //   3. array_filter(..., not empty) throw away blank pieces -> ['Drama', 'Sci-Fi']
    //   4. array_values(...)           renumber the list 0, 1, 2... (filtering leaves gaps)
    private static function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), fn($v) => $v !== ''));
    }
}
