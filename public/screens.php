<?php

require __DIR__ . '/../models/Showing.php';
require __DIR__ . '/../components/format-badge.php';

// Group the flat screen/showing rows into one entry per screen, dropping the placeholder
// showing row a LEFT JOIN produces when a screen has nothing scheduled.
$screensSchedule = [];
foreach (Showing::upcomingByScreen() as $row) {
    if (!isset($screensSchedule[$row['screen_id']])) {
        $screensSchedule[$row['screen_id']] = [
            'name' => $row['screen_name'],
            'type' => $row['screen_type'],
            'showings' => [],
        ];
    }
    if ($row['showing_id'] !== null) {
        $screensSchedule[$row['screen_id']]['showings'][] = $row;
    }
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
    <title>Screens - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/main.css">
</head>
<body class="site-body">
    <header class="site-header">
        <h1 class="site-title">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <?php foreach ($screensSchedule as $screen): ?>
            <section class="screen-schedule">
                <div class="screen-schedule__header">
                    <span class="format-badge <?= htmlspecialchars(formatBadgeClasses($screen['type']), ENT_QUOTES) ?>"><?= htmlspecialchars(formatBadgeLabel($screen['type']), ENT_QUOTES) ?></span>
                    <h2 class="screen-schedule__title"><?= htmlspecialchars($screen['name'], ENT_QUOTES) ?></h2>
                    <p class="screen-schedule__blurb"><?= htmlspecialchars(formatBlurb($screen['type']), ENT_QUOTES) ?></p>
                </div>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Movie</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($screen['showings'])): ?>
                            <tr>
                                <td class="schedule-table__empty" colspan="3">Nothing currently scheduled.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($screen['showings'] as $showing): ?>
                            <tr>
                                <td><a href="movie-details.php?id=<?= (int) $showing['movie_id'] ?>" class="schedule-table__movie-link"><?= htmlspecialchars($showing['movie_title'], ENT_QUOTES) ?></a></td>
                                <td class="schedule-table__muted"><?= htmlspecialchars(screenShowingDayLabel($showing['show_datetime']), ENT_QUOTES) ?></td>
                                <td><a href="#" class="showtime-button"><?= htmlspecialchars(date('g:i A', strtotime($showing['show_datetime'])), ENT_QUOTES) ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endforeach; ?>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
