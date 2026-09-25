<?php

require __DIR__ . '/../models/Movie.php';

$validStatuses = ['all', 'now_showing', 'not_scheduled', 'previously_shown'];
$status = in_array($_GET['status'] ?? '', $validStatuses, true) ? $_GET['status'] : 'all';

$movies = Movie::allWithStatus();
if ($status !== 'all') {
    $movies = array_filter($movies, fn($movie) => $movie['status'] === $status);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/nav-footer.css">
    <link rel="stylesheet" href="assets/css/movies.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title">Movies</h2>

            <div class="filter-tabs">
                <a href="?status=all" class="filter-tab <?= $status === 'all' ? 'filter-tab--active' : '' ?>">All Movies</a>
                <a href="?status=now_showing" class="filter-tab <?= $status === 'now_showing' ? 'filter-tab--active' : '' ?>">Now Showing</a>
                <a href="?status=not_scheduled" class="filter-tab <?= $status === 'not_scheduled' ? 'filter-tab--active' : '' ?>">Not Yet Scheduled</a>
                <a href="?status=previously_shown" class="filter-tab <?= $status === 'previously_shown' ? 'filter-tab--active' : '' ?>">Previously Shown</a>
            </div>

            <div class="movie-poster-grid">
                <?php if (empty($movies)): ?>
                    <p class="empty-message">No movies in this category.</p>
                <?php endif; ?>
                <?php foreach ($movies as $movie): ?>
                    <a href="movie-details.php?id=<?= (int) $movie['id'] ?>" class="movie-poster-card">
                        <?php if ($movie['poster_url']): ?>
                            <img src="<?= htmlspecialchars($movie['poster_url'], ENT_QUOTES) ?>" alt="" class="movie-poster-card__image">
                        <?php else: ?>
                            <div class="movie-poster-card__placeholder">Poster</div>
                        <?php endif; ?>
                        <h3 class="movie-poster-card__title"><?= htmlspecialchars($movie['title'], ENT_QUOTES) ?></h3>
                        <p class="movie-poster-card__metadata">
                            <?php
                            $meta = array_filter([
                                implode(', ', $movie['genres']),
                                $movie['duration_minutes'] ? $movie['duration_minutes'] . ' min' : null,
                            ]);
                            echo htmlspecialchars(implode(' · ', $meta), ENT_QUOTES);
                            ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
