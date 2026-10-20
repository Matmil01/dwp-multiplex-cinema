<?php
// Expects $movie in scope; shows a row of showtime pills if $movie['times'] is set,
// otherwise a single "Next: ..." line if $movie['next_show_datetime'] is set.
// $movie['show_format_badge'] (default true) lets a page that already names the format elsewhere skip it.
?>
<article class="movie-card">
    <div class="movie-card__content">
        <?php if ($movie['poster_url']): ?>
            <img src="<?= htmlspecialchars($movie['poster_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?> poster" class="movie-card__poster">
        <?php endif; ?>
        <div class="movie-card__details">
            <?php if ($movie['show_format_badge'] ?? true): ?>
                <span class="format-badge <?= htmlspecialchars(formatBadgeClasses($movie['screen_type']), ENT_QUOTES) ?>"><?= htmlspecialchars(formatBadgeLabel($movie['screen_type']), ENT_QUOTES) ?></span>
            <?php endif; ?>
            <h3 class="movie-card__title"><a href="/movie?id=<?= (int) $movie['movie_id'] ?>"><?= htmlspecialchars($movie['title'], ENT_QUOTES) ?></a></h3>
            <p class="movie-card__metadata"><?= htmlspecialchars(implode(' · ', array_filter([$movie['release_year'] ?? null, $movie['genres'], $movie['duration_minutes'] ? $movie['duration_minutes'] . ' min' : null])), ENT_QUOTES) ?></p>
            <?php if (isset($movie['times'])): ?>
                <div class="showtime-list">
                    <?php foreach ($movie['times'] as $time): ?>
                        <a href="#" class="showtime-button"><?= htmlspecialchars($time, ENT_QUOTES) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="movie-card__next-showing">Next: <?= htmlspecialchars(showingDayLabel($movie['next_show_datetime']), ENT_QUOTES) ?>, <?= htmlspecialchars(date('H:i', strtotime($movie['next_show_datetime'])), ENT_QUOTES) ?></p>
            <?php endif; ?>
        </div>
    </div>
</article>
