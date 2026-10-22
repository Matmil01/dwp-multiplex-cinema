<?php
require __DIR__ . '/../components/screen-format.php';
require __DIR__ . '/../components/day-label.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screens - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
    <link rel="stylesheet" href="/assets/css/showings-news.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <section class="hero hero--page hero--screens">
        <div class="hero__content">
            <h1 class="hero__title">Screens</h1>
        </div>
    </section>

    <main class="site-main">
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
                                    <td><a href="/movie?id=<?= (int) $movie['movie_id'] ?>" class="schedule-table__movie-link"><?= htmlspecialchars($movie['movie_title'], ENT_QUOTES) ?></a></td>
                                    <td class="schedule-table__muted"><?= htmlspecialchars(showingDayLabel($movie['next_show_datetime']), ENT_QUOTES) ?>, <?= htmlspecialchars(date('H:i', strtotime($movie['next_show_datetime'])), ENT_QUOTES) ?></td>
                                    <td><a href="/movie?id=<?= (int) $movie['movie_id'] ?>" class="showtime-button">View showtimes</a></td>
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
