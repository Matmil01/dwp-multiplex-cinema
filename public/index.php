<?php

require __DIR__ . '/../models/Showing.php';
require __DIR__ . '/../demo/AutoSchedule.php';
require __DIR__ . '/../components/format-badge.php';

AutoSchedule::ensureBuffer();

// Showing::today() returns one row per showing; group into one card per movie with a list of times.
$todayByMovie = [];
foreach (Showing::today() as $row) {
    if (!isset($todayByMovie[$row['movie_id']])) {
        $todayByMovie[$row['movie_id']] = $row;
        $todayByMovie[$row['movie_id']]['times'] = [];
    }
    $todayByMovie[$row['movie_id']]['times'][] = date('g:i A', strtotime($row['show_datetime']));
}
$todayMovies = array_values($todayByMovie);

$laterThisWeek = Showing::laterThisWeekPreview(4);

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
    <title>DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="site-body">
    <header class="site-header">
        <h1 class="site-title">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section class="home-section">
            <h2 class="section-title">Tonight's Program</h2>

            <div class="program-group">
                <h3 class="section-label">Today's Program</h3>
                <div class="movie-grid movie-grid--today">
                    <?php if (empty($todayMovies)): ?>
                        <p class="empty-message">No showings scheduled for today yet.</p>
                    <?php endif; ?>
                    <?php foreach ($todayMovies as $movie): ?>
                        <?php require __DIR__ . '/../components/movie-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                <a href="screens.php" class="text-link">See the full schedule &rarr;</a>
            </div>

            <div class="program-group">
                <h3 class="section-label">Later This Week</h3>
                <div class="movie-grid movie-grid--week">
                    <?php if (empty($laterThisWeek)): ?>
                        <p class="empty-message">Nothing else scheduled yet.</p>
                    <?php endif; ?>
                    <?php foreach ($laterThisWeek as $movie): ?>
                        <?php require __DIR__ . '/../components/movie-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                <a href="screens.php" class="text-link">See the full schedule &rarr;</a>
            </div>
        </section>

        <section class="home-section">
            <h2 class="section-title">About DWP Multiplex Cinema</h2>
            <p class="muted-text">Don't come. We hate people.</p>
        </section>

        <section class="home-section">
            <h2 class="section-title">News</h2>
            <div class="news-grid">
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">September 15, 2026</p>
                    <h3 class="news-card__title">Headline placeholder A</h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=1" class="text-link">Read more &rarr;</a>
                </article>
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">September 10, 2026</p>
                    <h3 class="news-card__title">Headline placeholder B</h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=2" class="text-link">Read more &rarr;</a>
                </article>
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">September 4, 2026</p>
                    <h3 class="news-card__title">Headline placeholder C</h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=3" class="text-link">Read more &rarr;</a>
                </article>
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">August 28, 2026</p>
                    <h3 class="news-card__title">Headline placeholder D</h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=4" class="text-link">Read more &rarr;</a>
                </article>
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">August 21, 2026</p>
                    <h3 class="news-card__title">Headline placeholder E</h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=5" class="text-link">Read more &rarr;</a>
                </article>
                <article class="news-card">
                    <div class="news-card__thumbnail">Thumbnail</div>
                    <p class="news-card__date">August 14, 2026</p>
                    <h3 class="news-card__title">Headline placeholder F</h3>
                    <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=6" class="text-link">Read more &rarr;</a>
                </article>
            </div>
            <a href="news.php" class="text-link">See all news &rarr;</a>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
