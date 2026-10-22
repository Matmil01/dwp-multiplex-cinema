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

    public function save(array $input, array $query): void
    {
        try {
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
            header('Location: /admin/movies?saved=' . urlencode(trim($input['title'] ?? '')));
            exit;
        } catch (Throwable $e) {
            $saveError = $e->getMessage();
        }

        // Saving failed: refill the editor with what you submitted, including any unsaved changes.
        $editor = array_merge(self::EMPTY_EDITOR, array_intersect_key($input, self::EMPTY_EDITOR));

        $this->showPage($query, $saveError, $editor);
    }

    public function delete(int $id): void
    {
        try {
            Movie::delete($id);
        } catch (RuntimeException $e) {
            header('Location: /admin/movies?delete_blocked=1');
            exit;
        }

        header('Location: /admin/movies?deleted=1');
        exit;
    }

    private function showPage(array $query, ?string $saveError, ?array $editor): void
    {
        $searchQuery = trim($query['q'] ?? '');
        $searchResults = [];
        $searchError = null;

        // Only load database or TMDB values when there are no failed-save edits to preserve.
        if ($editor === null && isset($query['tmdb_id'])) {
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

        require __DIR__ . '/../views/admin/movies.php';
    }
}
