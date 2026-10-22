<?php

class MovieController
{
    public function index(string $requestedStatus): void
    {
        $validStatuses = ['all', 'now_showing', 'not_scheduled', 'previously_shown'];
        $status = in_array($requestedStatus, $validStatuses, true) ? $requestedStatus : 'all';

        $movies = Movie::allWithStatus();
        if ($status !== 'all') {
            $movies = array_filter($movies, fn($movie) => $movie['status'] === $status);
        }

        require __DIR__ . '/../views/movies.php';
    }

    public function show(int $id): void
    {
        $movie = Movie::find($id);

        if (!$movie) {
            http_response_code(404);
            $title = 'Movie not found';
            $message = 'That movie is not in our catalog.';
            require __DIR__ . '/../views/error.php';
            return;
        }

        $showings = Showing::upcomingForMovie($movie->id);

        require __DIR__ . '/../views/movie-details.php';
    }
}
