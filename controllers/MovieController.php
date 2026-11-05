<?php

class MovieController
{
    // $requestedStatus is whatever came after ?status= in the address (or "" if nothing).
    public function index(string $requestedStatus): void
    {
        // Never trust a value from the address. If it is not one of our four known filter
        // names, quietly fall back to 'all'. in_array() = "is this value in that list?";
        // the `true` makes it a strict comparison (text must match exactly).
        $validStatuses = ['all', 'now_showing', 'not_scheduled', 'previously_shown'];
        $status = in_array($requestedStatus, $validStatuses, true) ? $requestedStatus : 'all';

        // Ask the model for every movie, each tagged with its status (see Movie::allWithStatus).
        $movies = Movie::allWithStatus();
        // If a filter tab is active, keep only the movies whose status matches.
        // `fn($movie) => ...` is a one-line function that array_filter runs on each movie;
        // movies for which it gives false are dropped.
        if ($status !== 'all') {
            $movies = array_filter($movies, fn($movie) => $movie['status'] === $status);
        }

        // Load the view. It uses $movies and $status, which exist right here.
        require __DIR__ . '/../views/movies.php';
    }

    // $id is the number after ?id= in the address, already forced to a whole number by index.php.
    public function show(int $id): void
    {
        $movie = Movie::find($id);

        // Movie::find() gives null when no movie has that id (e.g. someone typed ?id=999).
        // Send a proper 404 status so the browser (and search engines) know it's not a real page,
        // then show the shared error view with a message and stop.
        if (!$movie) {
            http_response_code(404);
            $title = 'Movie not found';
            $message = 'That movie is not in our catalog.';
            require __DIR__ . '/../views/error.php';
            return;
        }

        // Also fetch the future showings for this movie, so the page can list them with
        // "Book" buttons. $movie->id uses -> because Movie::find() returned an object.
        $showings = Showing::upcomingForMovie($movie->id);

        require __DIR__ . '/../views/movie-details.php';
    }
}
