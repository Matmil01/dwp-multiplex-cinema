<?php

session_start();
require __DIR__ . '/../models/Showing.php';
require __DIR__ . '/../components/screen-format.php';

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

// "Today"/"Tomorrow" instead of the raw date, otherwise weekday + date (same idea as movie-details.php).
function screenShowingDayLabel(string $showDatetime): string
{
    $day = date('Y-m-d', strtotime($showDatetime));
    if ($day === date('Y-m-d')) {
        return 'Today';
    }
    if ($day === date('Y-m-d', strtotime('+1 day'))) {
        return 'Tomorrow';
    }
    return date('D, M j', strtotime($showDatetime));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screens - Untitled Cinema</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/nav-footer.css">
    <link rel="stylesheet" href="assets/css/showings-news.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <header class="screens-page-header">
            <h1 class="screens-page-header__title">Screens &amp; schedule</h1>
        </header>

        <div class="screen-schedules">
            <?php if (empty($screensSchedule)): ?>
                <p class="screens-empty">No upcoming screenings are scheduled right now.</p>
            <?php endif; ?>
            <?php foreach ($screensSchedule as $screen): ?>
                <section class="screen-schedule">
                    <div class="screen-schedule__header">
                        <div class="screen-schedule__intro">
                            <h2 class="screen-schedule__title"><?= htmlspecialchars(screenDisplayName($screen['type']), ENT_QUOTES) ?></h2>
                            <p class="screen-schedule__blurb"><?= htmlspecialchars(formatBlurb($screen['type']), ENT_QUOTES) ?></p>
                        </div>
                    </div>
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th>Movie</th>
                                <th>Next Showing</th>
                                <th><span class="visually-hidden">Showtimes</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($screen['movies'])): ?>
                                <tr>
                                    <td class="schedule-table__empty" colspan="3">Nothing currently scheduled.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($screen['movies'] as $movie): ?>
                                <tr>
                                    <td><a href="movie-details.php?id=<?= (int) $movie['movie_id'] ?>" class="schedule-table__movie-link"><?= htmlspecialchars($movie['movie_title'], ENT_QUOTES) ?></a></td>
                                    <td class="schedule-table__muted"><?= htmlspecialchars(screenShowingDayLabel($movie['next_show_datetime']), ENT_QUOTES) ?>, <?= htmlspecialchars(date('H:i', strtotime($movie['next_show_datetime'])), ENT_QUOTES) ?></td>
                                    <td><a href="movie-details.php?id=<?= (int) $movie['movie_id'] ?>" class="showtime-button">View showtimes</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endforeach; ?>
        </div>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
