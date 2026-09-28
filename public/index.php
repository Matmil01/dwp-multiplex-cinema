<?php

require __DIR__ . '/../models/Showing.php';
require __DIR__ . '/../demo/AutoSchedule.php';
require __DIR__ . '/../components/screen-format.php';

$config = require __DIR__ . '/../config/config.php';
if ($config['auto_schedule_enabled']) {
    AutoSchedule::ensureBuffer();
}

// Showing::today() returns one row per showing; group into one card per movie with a list of times.
$todayByMovie = [];
foreach (Showing::today() as $row) {
    if (!isset($todayByMovie[$row['movie_id']])) {
        $todayByMovie[$row['movie_id']] = $row;
        $todayByMovie[$row['movie_id']]['times'] = [];
    }
    $todayByMovie[$row['movie_id']]['times'][] = date('H:i', strtotime($row['show_datetime']));
}
$todayMovies = array_values($todayByMovie);

$laterThisWeek = Showing::laterThisWeekPreview(6);

// "Tomorrow" instead of the raw date, otherwise the weekday name (same idea as movie-details.php).
function showingDayLabel(string $showDatetime): string
{
    if (date('Y-m-d', strtotime($showDatetime)) === date('Y-m-d', strtotime('+1 day'))) {
        return 'Tomorrow';
    }
    return date('D', strtotime($showDatetime));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Untitled Cinema</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/nav-footer.css">
    <link rel="stylesheet" href="assets/css/movies.css">
    <link rel="stylesheet" href="assets/css/showings-news.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section class="home-section">
            <h2 class="section-title section-title--primary">Tonight's Program</h2>

            <div class="program-group">
                <div class="movie-grid movie-grid--today">
                    <?php if (empty($todayMovies)): ?>
                        <p class="empty-message">No showings scheduled for today yet.</p>
                    <?php endif; ?>
                    <?php foreach ($todayMovies as $movie): ?>
                        <?php require __DIR__ . '/../components/movie-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                <a href="screens.php" class="ctabutton">See the full schedule &rarr;</a>
            </div>

            <div class="program-group">
                <h2 class="section-title">Later This Week</h2>
                <div class="movie-grid movie-grid--week">
                    <?php if (empty($laterThisWeek)): ?>
                        <p class="empty-message">Nothing else scheduled yet.</p>
                    <?php endif; ?>
                    <?php foreach ($laterThisWeek as $movie): ?>
                        <?php require __DIR__ . '/../components/movie-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                <a href="screens.php" class="ctabutton">See the full schedule &rarr;</a>
            </div>
        </section>

        <section class="home-section">
            <h2 class="section-title section-title--primary">About <span class="section-title__brand">Untitled Cinema</span></h2>
            <p class="muted-text">Don't come. We hate people.</p>
        </section>

        <section class="home-section">
            <h2 class="section-title section-title--primary">News</h2>
            <div class="news-grid">
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">September 15, 2026</p>
                    <h3 class="news-card__title"><a href="news-article.php?id=1">Headline placeholder A</a></h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=1" class="text-link">Read more &rarr;</a>
                </article>
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">September 10, 2026</p>
                    <h3 class="news-card__title"><a href="news-article.php?id=2">Headline placeholder B</a></h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=2" class="text-link">Read more &rarr;</a>
                </article>
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">September 4, 2026</p>
                    <h3 class="news-card__title"><a href="news-article.php?id=3">Headline placeholder C</a></h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=3" class="text-link">Read more &rarr;</a>
                </article>
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">August 28, 2026</p>
                    <h3 class="news-card__title"><a href="news-article.php?id=4">Headline placeholder D</a></h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=4" class="text-link">Read more &rarr;</a>
                </article>
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">August 21, 2026</p>
                    <h3 class="news-card__title"><a href="news-article.php?id=5">Headline placeholder E</a></h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=5" class="text-link">Read more &rarr;</a>
                </article>
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">August 14, 2026</p>
                    <h3 class="news-card__title"><a href="news-article.php?id=6">Headline placeholder F</a></h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=6" class="text-link">Read more &rarr;</a>
                </article>
            </div>
            <a href="news.php" class="ctabutton">See all news &rarr;</a>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
