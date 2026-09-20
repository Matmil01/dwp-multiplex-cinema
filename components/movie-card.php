<?php
// Expects $movie in scope; shows a row of showtime pills if $movie['times'] is set,
// otherwise a single "Next: ..." line if $movie['next_show_datetime'] is set.
?>
<div class="bg-gray-900 rounded-lg p-4">
    <div class="flex gap-4 items-center">
        <img src="<?= htmlspecialchars($movie['poster_url'] ?: 'assets/images/movie.jpg', ENT_QUOTES) ?>" alt="<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?> poster" class="w-1/2 aspect-2/3 object-cover rounded-lg shrink-0">
        <div class="flex-1">
            <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border <?= htmlspecialchars(formatBadgeClasses($movie['screen_type']), ENT_QUOTES) ?> mb-2"><?= htmlspecialchars($movie['screen_name'], ENT_QUOTES) ?></span>
            <h3 class="font-semibold mb-2"><a href="movie-details.php?id=<?= (int) $movie['movie_id'] ?>" class="hover:underline"><?= htmlspecialchars($movie['title'], ENT_QUOTES) ?></a></h3>
            <p class="text-gray-400 text-base"><?= htmlspecialchars(implode(' · ', array_filter([$movie['genres'], $movie['duration_minutes'] ? $movie['duration_minutes'] . ' min' : null])), ENT_QUOTES) ?></p>
            <?php if (isset($movie['times'])): ?>
                <div class="flex flex-wrap gap-2 mt-3">
                    <?php foreach ($movie['times'] as $time): ?>
                        <a href="#" class="text-xs bg-gray-800 hover:bg-blue-900 rounded px-2 py-1"><?= htmlspecialchars($time, ENT_QUOTES) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-sm mt-1">Next: <?= htmlspecialchars(showingDayLabel($movie['next_show_datetime']), ENT_QUOTES) ?>, <?= htmlspecialchars(date('g:i A', strtotime($movie['next_show_datetime'])), ENT_QUOTES) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
