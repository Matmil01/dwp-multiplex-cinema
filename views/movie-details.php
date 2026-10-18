<?php
require __DIR__ . '/../components/screen-format.php';

// "Today"/"Tomorrow" instead of the raw date, otherwise just the date.
function showingDayLabel(string $showDatetime): string
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
    <title><?= htmlspecialchars($movie->title, ENT_QUOTES) ?> - Untitled Cinema</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/nav-footer.css">
    <link rel="stylesheet" href="assets/css/movies.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section class="movie-details">
            <?php if ($movie->poster_url): ?>
                <img src="<?= htmlspecialchars($movie->poster_url, ENT_QUOTES) ?>" alt="" class="movie-details__poster">
            <?php else: ?>
                <div class="movie-details__poster movie-details__poster--placeholder">Poster</div>
            <?php endif; ?>
            <div>
                <h2 class="movie-details__title"><?= htmlspecialchars($movie->title, ENT_QUOTES) ?></h2>
                <p class="movie-details__metadata"><?= htmlspecialchars($movie->metadataLine(), ENT_QUOTES) ?></p>
                <p class="movie-details__description"><?= nl2br(htmlspecialchars($movie->description ?? '', ENT_QUOTES)) ?></p>
                <dl class="movie-credits">
                    <?php if ($movie->director): ?>
                        <div class="movie-credits__row">
                            <dt class="movie-credits__label">Director</dt>
                            <dd class="movie-credits__value"><?= htmlspecialchars($movie->director, ENT_QUOTES) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($movie->writers)): ?>
                        <div class="movie-credits__row">
                            <dt class="movie-credits__label">Writers</dt>
                            <dd class="movie-credits__value"><?= htmlspecialchars(implode(', ', $movie->writers), ENT_QUOTES) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($movie->cast)): ?>
                        <div class="movie-credits__row">
                            <dt class="movie-credits__label">Cast</dt>
                            <dd class="movie-credits__value"><?= htmlspecialchars(implode(', ', $movie->cast), ENT_QUOTES) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </section>

        <section>
            <h2 class="section-title">Showings</h2>
            <div class="showing-list">
                <?php if (empty($showings)): ?>
                    <p class="empty-message">No upcoming showings scheduled.</p>
                <?php endif; ?>
                <?php foreach ($showings as $showing): ?>
                    <div class="showing-card">
                        <div>
                            <p class="showing-card__time"><?= htmlspecialchars(showingDayLabel($showing['show_datetime']), ENT_QUOTES) ?>, <?= htmlspecialchars(date('H:i', strtotime($showing['show_datetime'])), ENT_QUOTES) ?></p>
                            <p class="showing-card__screen"><?= htmlspecialchars(screenDisplayName($showing['screen_type']), ENT_QUOTES) ?></p>
                        </div>
                        <a href="#" class="base-button">Book</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
