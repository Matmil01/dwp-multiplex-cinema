<?php
require __DIR__ . '/../components/screen-format.php';

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

    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title">Untitled Cinema</h1>
            <p class="hero__tagline"><?= htmlspecialchars($company['tagline'], ENT_QUOTES) ?></p>
            <?php
            $ticketHref = 'screens.php';
            $ticketLabel = 'See the full schedule';
            require __DIR__ . '/../components/cta-button.php';
            ?>
        </div>
    </section>

    <main class="site-main">
        <section class="home-section">
            <h2 class="section-title section-title--primary">Tonight's Program</h2>

            <div class="program-group">
                <div class="format-rows">
                    <?php foreach ($screens as $screen): ?>
                        <?php $formatMovies = $screen['movies']; ?>
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
                <?php
                $ticketHref = 'screens.php';
                $ticketLabel = 'See the full schedule';
                require __DIR__ . '/../components/cta-button.php';
                ?>
            </div>
        </section>

        <section class="home-section about">
            <div class="about__text">
                <h2 class="section-title section-title--primary">About <span class="section-title__brand">Untitled Cinema</span></h2>
                <p class="muted-text"><?= nl2br(htmlspecialchars($company['description'], ENT_QUOTES)) ?></p>
            </div>
            <img class="about__image" src="assets/images/About-Section-right.jpeg" alt="">
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
