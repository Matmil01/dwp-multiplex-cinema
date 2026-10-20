<?php

class HomeController
{
    public function index(): void
    {
        $config = require __DIR__ . '/../config/config.php';
        if ($config['auto_schedule_enabled']) {
            AutoSchedule::ensureBuffer();
        }

        $company = CompanyInfo::find();

        // Collect today's showings into one card per movie and format, with a list of times.
        $todayByMovieAndFormat = [];
        foreach (Showing::today() as $row) {
            // Showings that already started are left off, same as on the movie pages.
            if (!Booking::isOpen($row)) {
                continue;
            }

            // For example, "12:35mm" and "12:imax" keep the same movie's formats separate.
            $movieFormatKey = $row['movie_id'] . ':' . $row['screen_type'];
            if (!isset($todayByMovieAndFormat[$movieFormatKey])) {
                $todayByMovieAndFormat[$movieFormatKey] = $row;
                $todayByMovieAndFormat[$movieFormatKey]['times'] = [];
            }
            // Add this time only to the card for this movie in this format; the id lets the pill link to booking.
            $todayByMovieAndFormat[$movieFormatKey]['times'][] = [
                'showing_id' => $row['id'],
                'label' => date('H:i', strtotime($row['show_datetime'])),
            ];
        }
        $todayMovies = array_values($todayByMovieAndFormat);

        // Attach each screen's own movies, so the view can loop without filtering.
        $screens = array_map(
            fn($screen) => $screen + ['movies' => array_values(array_filter($todayMovies, fn($m) => $m['screen_type'] === $screen['type']))],
            Showing::screens()
        );

        $laterThisWeek = Showing::laterThisWeekPreview(6);
        $latestNews = array_slice(News::all(), 0, 6);

        require __DIR__ . '/../views/index.php';
    }
}
