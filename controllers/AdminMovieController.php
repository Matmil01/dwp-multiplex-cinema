<?php

class AdminMovieController
{
    // Every field the editor form shows, with its blank value; other arrays are laid over this.
    private const EMPTY_EDITOR = [
        'id' => null,
        'tmdb_id' => null,
        'title' => '',
        'description' => '',
        'duration_minutes' => null,
        'poster_url' => '',
        'release_year' => null,
        'genres' => '',
        'director' => '',
        'writers' => '',
        'cast' => '',
    ];

    public function index(array $query): void
    {
        $this->showPage($query, null, null);
    }

    public function handlePost(array $post, array $query): void
    {
        $action = $post['action'] ?? '';

        if ($action === 'save') {
            $this->save($post, $query);
        } elseif ($action === 'delete') {
            $this->delete((int) ($post['id'] ?? 0));
        } else {
            $this->index($query);
        }
    }

    // Throws a RuntimeException with a readable reason for the first rule the input breaks.
    private function validate(array $input): void
    {
        $title = trim($input['title'] ?? '');
        if ($title === '' || mb_strlen($title) > 100) {
            throw new RuntimeException('Title is required (max 100 characters).');
        }

        $year = trim($input['release_year'] ?? '');
        if (!ctype_digit($year) || (int) $year < 1888 || (int) $year > 2100) {
            throw new RuntimeException('Release year must be between 1888 and 2100.');
        }

        // Blank runtime is allowed; a movie without one just can't be scheduled yet.
        $duration = trim($input['duration_minutes'] ?? '');
        if ($duration !== '' && (!ctype_digit($duration) || (int) $duration < 1 || (int) $duration > 1000)) {
            throw new RuntimeException('Runtime must be a whole number of minutes between 1 and 1000.');
        }

        foreach (['id', 'tmdb_id'] as $field) {
            $value = trim($input[$field] ?? '');
            if ($value !== '' && !ctype_digit($value)) {
                throw new RuntimeException('Invalid movie id.');
            }
        }

        $poster = trim($input['poster_url'] ?? '');
        $posterScheme = strtolower((string) parse_url($poster, PHP_URL_SCHEME));
        if ($poster !== '' && (mb_strlen($poster) > 150 || !filter_var($poster, FILTER_VALIDATE_URL) || !in_array($posterScheme, ['http', 'https'], true))) {
            throw new RuntimeException('Poster must be a web address (max 150 characters).');
        }

        if (mb_strlen($input['description'] ?? '') > 5000) {
            throw new RuntimeException('Description is too long (max 5000 characters).');
        }

        // Limits follow the column sizes in schema.sql (genres.name 20, people.name 100).
        $lists = [
            ['Genre', $input['genres'] ?? '', 20],
            ['Director', $input['director'] ?? '', 100],
            ['Writer', $input['writers'] ?? '', 100],
            ['Cast member', $input['cast'] ?? '', 100],
        ];
        foreach ($lists as [$label, $value, $max]) {
            foreach (explode(',', $value) as $name) {
                if (mb_strlen(trim($name)) > $max) {
                    throw new RuntimeException("$label names can be at most $max characters.");
                }
            }
        }
    }

    public function save(array $input, array $query): void
    {
        try {
            $this->validate($input);

            // Clean each form field before handing it to the model. Pattern used on each line:
            // `($input['x'] ?? '') !== '' ? (int) $input['x'] : null` = "if the field was filled
            // in, make it a number; if left blank, store NULL". Blank text fields are trimmed.
            Movie::save([
                'id' => ($input['id'] ?? '') !== '' ? (int) $input['id'] : null,
                'tmdb_id' => ($input['tmdb_id'] ?? '') !== '' ? (int) $input['tmdb_id'] : null,
                'title' => trim($input['title'] ?? ''),
                'description' => trim($input['description'] ?? ''),
                'duration_minutes' => ($input['duration_minutes'] ?? '') !== '' ? (int) $input['duration_minutes'] : null,
                'poster_url' => trim($input['poster_url'] ?? ''),
                'release_year' => ($input['release_year'] ?? '') !== '' ? (int) $input['release_year'] : null,
                'genres' => $input['genres'] ?? '',
                'director' => $input['director'] ?? '',
                'writers' => $input['writers'] ?? '',
                'cast' => $input['cast'] ?? '',
            ]);
            // urlencode() makes the title safe to put in an address (spaces -> %20 etc.).
            header('Location: /admin/movies?saved=' . urlencode(trim($input['title'] ?? '')));
            exit;
        } catch (PDOException $e) {
            // Database errors go to the log and the generic error page, not onto this page.
            throw $e;
        } catch (RuntimeException $e) {
            // Movie::save() threw (e.g. "runtime would make showings overlap"): keep its message.
            $saveError = $e->getMessage();
        }

        // Saving failed: refill the editor with what you submitted, including any unsaved changes.
        // array_intersect_key() keeps only the $input keys that exist in EMPTY_EDITOR, so stray
        // form fields (like 'action') don't leak into the editor.
        $editor = array_merge(self::EMPTY_EDITOR, array_intersect_key($input, self::EMPTY_EDITOR));

        $this->showPage($query, $saveError, $editor);
    }

    public function delete(int $id): void
    {
        // Movie::delete() throws when bookings exist for the movie's showings; the redirect
        // flag turns that into a red snackbar on the page.
        try {
            Movie::delete($id);
        } catch (RuntimeException $e) {
            header('Location: /admin/movies?delete_blocked=1');
            exit;
        }

        header('Location: /admin/movies?deleted=1');
        exit;
    }

    // Gathers everything the view needs and loads it. $saveError and $editor are only set when
    // coming from a failed save; otherwise the editor is filled from the address (?tmdb_id / ?edit_id).
    private function showPage(array $query, ?string $saveError, ?array $editor): void
    {
        $searchQuery = trim($query['q'] ?? '');
        $searchResults = [];
        $searchError = null;

        // Only load database or TMDB values when there are no failed-save edits to preserve.
        if ($editor === null && isset($query['tmdb_id'])) {
            // Import: ask TMDB for the movie's full details and reshape them into form fields.
            // TMDB gives lists of people; implode()/array_column() flatten them into "Name, Name" text.
            try {
                $details = (new TmdbAdapter())->getMovieDetails((int) $query['tmdb_id']);
                $editor = array_merge(self::EMPTY_EDITOR, array_intersect_key($details, self::EMPTY_EDITOR), [
                    'genres' => implode(', ', $details['genres']),
                    'director' => $details['director']['name'] ?? '',
                    'writers' => implode(', ', array_column($details['writers'], 'name')),
                    'cast' => implode(', ', array_column($details['cast'], 'name')),
                ]);
            } catch (RuntimeException $e) {
                $searchError = $e->getMessage();
            }
        } elseif ($editor === null && isset($query['edit_id'])) {
            // Edit: load one of our own movies. get_object_vars() turns the Movie object back
            // into a plain list (property name => value) so it can be merged like the others.
            $movie = Movie::find((int) $query['edit_id']);
            if ($movie) {
                $editor = array_merge(self::EMPTY_EDITOR, array_intersect_key(get_object_vars($movie), self::EMPTY_EDITOR), [
                    'genres' => implode(', ', $movie->genres),
                    'director' => $movie->director ?? '',
                    'writers' => implode(', ', $movie->writers),
                    'cast' => implode(', ', $movie->cast),
                ]);
            }
        }

        // A search box submission (?q=...): ask TMDB. Network problems become a message, not a crash.
        if ($searchQuery !== '') {
            try {
                $searchResults = (new TmdbAdapter())->searchMovies($searchQuery);
            } catch (RuntimeException $e) {
                $searchError = $e->getMessage();
            }
        }

        if ($editor === null) {
            $editor = self::EMPTY_EDITOR;
        }

        $catalog = Movie::all();

        // Pick the one snackbar message to show (see components/snackbar.php), if any.
        if ($saveError) {
            $snackbarType = 'error';
            $snackbarMessage = 'Save failed: ' . $saveError;
        } elseif (isset($query['saved'])) {
            $snackbarType = 'success';
            $snackbarMessage = $query['saved'] . ' saved.';
        } elseif (isset($query['deleted'])) {
            $snackbarType = 'success';
            $snackbarMessage = 'Movie deleted.';
        } elseif (isset($query['delete_blocked'])) {
            $snackbarType = 'error';
            $snackbarMessage = 'This movie has showings with bookings and can\'t be deleted.';
        }

        require __DIR__ . '/../templates/admin/movies.php';
    }
}
