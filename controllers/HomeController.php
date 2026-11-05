<?php

class HomeController
{
    public function index(): void
    {
        // Demo helper
        $config = require __DIR__ . '/../config/config.php';
        if ($config['auto_schedule_enabled']) {
            AutoSchedule::ensureBuffer();
        }

        $company = CompanyInfo::find();
        $todayByMovieAndFormat = [];
        foreach (Showing::today() as $row) {
            if (!Booking::isOpen($row)) {
                continue;
            }

            $movieFormatKey = $row['movie_id'] . ':' . $row['screen_type'];
            if (!isset($todayByMovieAndFormat[$movieFormatKey])) {
                $todayByMovieAndFormat[$movieFormatKey] = $row;
                $todayByMovieAndFormat[$movieFormatKey]['times'] = [];
            }
            $todayByMovieAndFormat[$movieFormatKey]['times'][] = [
                'showing_id' => $row['id'],
                'label' => date('H:i', strtotime($row['show_datetime'])),
            ];
        }
        $todayMovies = array_values($todayByMovieAndFormat);

        $screens = array_map(
            fn($screen) => $screen + ['movies' => array_values(array_filter($todayMovies, fn($m) => $m['screen_type'] === $screen['type']))],
            Showing::screens()
        );

        $laterThisWeek = Showing::laterThisWeekPreview(6);
        $latestNews = array_slice(News::all(), 0, 6);

        require __DIR__ . '/../templates/index.php';
    }
}
