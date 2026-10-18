<?php

require_once __DIR__ . '/../models/Showing.php';

class ScreenController
{
    public function index(): void
    {
        // Group the flat screen/showing rows into one entry per screen, dropping the placeholder
        // showing row a LEFT JOIN produces when a screen has nothing scheduled. Only the earliest
        // upcoming showing per movie is kept (rows arrive ordered by show_datetime) — the full list of
        // dates/times for a movie already lives on its movie-details.php page, so this page just says
        // what's playing in each screen/format and links out for the full timetable.
        $screensSchedule = [];
        foreach (Showing::upcomingByScreen() as $row) {
            if (!isset($screensSchedule[$row['screen_id']])) {
                $screensSchedule[$row['screen_id']] = [
                    'type' => $row['screen_type'],
                    'movies' => [],
                ];
            }
            if ($row['showing_id'] === null || isset($screensSchedule[$row['screen_id']]['movies'][$row['movie_id']])) {
                continue;
            }
            $screensSchedule[$row['screen_id']]['movies'][$row['movie_id']] = [
                'movie_id' => $row['movie_id'],
                'movie_title' => $row['movie_title'],
                'next_show_datetime' => $row['show_datetime'],
            ];
        }

        require __DIR__ . '/../views/screens.php';
    }
}
