<?php

require __DIR__ . '/../models/Showing.php';
require __DIR__ . '/../models/CompanyInfo.php';
require __DIR__ . '/../models/News.php';
require __DIR__ . '/../demo/AutoSchedule.php';
require __DIR__ . '/../components/screen-format.php';

$config = require __DIR__ . '/../config/config.php';
if ($config['auto_schedule_enabled']) {
    AutoSchedule::ensureBuffer();
}

$company = CompanyInfo::find();

// Collect today's showings into one card per movie and format, with a list of times.
$todayByMovieAndFormat = [];
foreach (Showing::today() as $row) {
    // For example, "12:35mm" and "12:imax" keep the same movie's formats separate.
    $movieFormatKey = $row['movie_id'] . ':' . $row['screen_type'];
    if (!isset($todayByMovieAndFormat[$movieFormatKey])) {
        $todayByMovieAndFormat[$movieFormatKey] = $row;
        $todayByMovieAndFormat[$movieFormatKey]['times'] = [];
    }
    // Add this time only to the card for this movie in this format.
    $todayByMovieAndFormat[$movieFormatKey]['times'][] = date('H:i', strtotime($row['show_datetime']));
}
$todayMovies = array_values($todayByMovieAndFormat);

$laterThisWeek = Showing::laterThisWeekPreview(6);
$latestNews = array_slice(News::all(), 0, 6);

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
                <div class="format-rows">
                    <?php foreach (Showing::screens() as $screen): ?>
                        <?php $formatMovies = array_values(array_filter($todayMovies, fn($m) => $m['screen_type'] === $screen['type'])); ?>
                        <div class="format-row">
                            <div class="format-row__info">
                                <h3 class="format-row__title"><?= htmlspecialchars(screenDisplayName($screen['type']), ENT_QUOTES) ?></h3>
                                <p class="format-row__blurb"><?= htmlspecialchars(formatBlurb($screen['type']), ENT_QUOTES) ?></p>
                            </div>
                            <div class="format-row__movies">
                                <?php if (empty($formatMovies)): ?>
                                    <p class="empty-message">No <?= htmlspecialchars(formatBadgeLabel($screen['type']), ENT_QUOTES) ?> showings tonight.</p>
                                <?php endif; ?>
                                <?php foreach ($formatMovies as $movie): ?>
                                    <?php $movie['show_format_badge'] = false; ?>
                                    <?php require __DIR__ . '/../components/movie-card.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php
                $ticketHref = 'screens.php';
                $ticketLabel = 'See the full schedule';
                require __DIR__ . '/../components/cta-button.php';
                ?>
            </div>

            <div class="program-group">
                <h2 class="section-title">Later This Week</h2>
                <div class="movie-grid movie-grid--today">
                    <?php if (empty($laterThisWeek)): ?>
                        <p class="empty-message">Nothing else scheduled yet.</p>
                    <?php endif; ?>
                    <?php foreach ($laterThisWeek as $movie): ?>
                        <?php require __DIR__ . '/../components/movie-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                <?php require __DIR__ . '/../components/cta-button.php'; ?>
            </div>
        </section>

        <section class="home-section">
            <h2 class="section-title section-title--primary">About <span class="section-title__brand">Untitled Cinema</span></h2>
            <p class="muted-text"><?= nl2br(htmlspecialchars($company['description'], ENT_QUOTES)) ?></p>
        </section>

        <section class="home-section">
            <h2 class="section-title section-title--primary">News</h2>
            <div class="news-grid">
                <?php if (empty($latestNews)): ?>
                    <p class="empty-message">No news yet &mdash; check back soon.</p>
                <?php endif; ?>
                <?php foreach ($latestNews as $article): ?>
                    <article class="news-card">
                        <?php if ($article['image_path']): ?>
                            <img src="<?= htmlspecialchars($article['image_path'], ENT_QUOTES) ?>" alt="" class="news-card__thumbnail">
                        <?php else: ?>
                            <div class="news-card__thumbnail">Thumbnail</div>
                        <?php endif; ?>
                        <h3 class="news-card__title"><a href="news-article.php?id=<?= (int) $article['id'] ?>"><?= htmlspecialchars($article['title'], ENT_QUOTES) ?></a></h3>
                        <p class="news-card__date"><?= date('F j, Y', strtotime($article['published_at'])) ?></p>
                        <p class="news-card__excerpt"><?= htmlspecialchars(News::excerpt($article['body']), ENT_QUOTES) ?></p>
                        <a href="news-article.php?id=<?= (int) $article['id'] ?>" class="text-link">Read more &rarr;</a>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php
            $ticketHref = 'news.php';
            $ticketLabel = 'See all news';
            require __DIR__ . '/../components/cta-button.php';
            ?>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
