<?php

require_once __DIR__ . '/../classes/TmdbAdapter.php';
require_once __DIR__ . '/../models/Movie.php';

class AdminMovieController
{
    public function index(array $query): void
    {
        $this->showPage($query, null, null);
    }

    public function save(array $input, array $query): void
    {
        try {
            Movie::save([
                'id' => $input['id'] !== '' ? (int) $input['id'] : null,
                'tmdb_id' => $input['tmdb_id'] !== '' ? (int) $input['tmdb_id'] : null,
                'title' => trim($input['title'] ?? ''),
                'description' => trim($input['description'] ?? ''),
                'duration_minutes' => $input['duration_minutes'] !== '' ? (int) $input['duration_minutes'] : null,
                'poster_url' => trim($input['poster_url'] ?? ''),
                'release_year' => $input['release_year'] !== '' ? (int) $input['release_year'] : null,
                'genres' => $input['genres'] ?? '',
                'director' => $input['director'] ?? '',
                'writers' => $input['writers'] ?? '',
                'cast' => $input['cast'] ?? '',
            ]);
            header('Location: movies.php?saved=' . urlencode(trim($input['title'] ?? '')));
            exit;
        } catch (Throwable $e) {
            $saveError = $e->getMessage();
        }

        // Saving failed: refill the editor with what you submitted, including any unsaved changes.
        $editor = [
            'id' => $input['id'] ?? null,
            'tmdb_id' => $input['tmdb_id'] ?? null,
            'title' => $input['title'] ?? '',
            'description' => $input['description'] ?? '',
            'duration_minutes' => $input['duration_minutes'] ?? '',
            'poster_url' => $input['poster_url'] ?? '',
            'release_year' => $input['release_year'] ?? '',
            'genres' => $input['genres'] ?? '',
            'director' => $input['director'] ?? '',
            'writers' => $input['writers'] ?? '',
            'cast' => $input['cast'] ?? '',
        ];

        $this->showPage($query, $saveError, $editor);
    }

    public function delete(int $id): void
    {
        Movie::delete($id);
        header('Location: movies.php?deleted=1');
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
                $editor = [
                    'id' => null,
                    'tmdb_id' => $details['tmdb_id'],
                    'title' => $details['title'],
                    'description' => $details['description'],
                    'duration_minutes' => $details['duration_minutes'],
                    'poster_url' => $details['poster_url'],
                    'release_year' => $details['release_year'],
                    'genres' => implode(', ', $details['genres']),
                    'director' => $details['director']['name'] ?? '',
                    'writers' => implode(', ', array_column($details['writers'], 'name')),
                    'cast' => implode(', ', array_column($details['cast'], 'name')),
                ];
            } catch (RuntimeException $e) {
                $searchError = $e->getMessage();
            }
        } elseif ($editor === null && isset($query['edit_id'])) {
            $movie = Movie::find((int) $query['edit_id']);
            if ($movie) {
                $editor = [
                    'id' => $movie->id,
                    'tmdb_id' => $movie->tmdb_id,
                    'title' => $movie->title,
                    'description' => $movie->description,
                    'duration_minutes' => $movie->duration_minutes,
                    'poster_url' => $movie->poster_url,
                    'release_year' => $movie->release_year,
                    'genres' => implode(', ', $movie->genres),
                    'director' => $movie->director ?? '',
                    'writers' => implode(', ', $movie->writers),
                    'cast' => implode(', ', $movie->cast),
                ];
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
            $editor = [
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
        }

        require __DIR__ . '/../views/admin/movies.php';
    }
}
