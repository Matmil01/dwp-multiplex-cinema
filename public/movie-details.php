<?php

require __DIR__ . '/../models/Movie.php';
require __DIR__ . '/../models/Showing.php';

$movie = Movie::find((int) ($_GET['id'] ?? 0));

if (!$movie) {
    http_response_code(404);
    echo 'Movie not found.';
    exit;
}

$showings = Showing::upcomingForMovie($movie['id']);

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
    <title><?= htmlspecialchars($movie['title'], ENT_QUOTES) ?> - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../src/components/public-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section class="flex flex-col sm:flex-row gap-6">
            <?php if ($movie['poster_url']): ?>
                <img src="<?= htmlspecialchars($movie['poster_url'], ENT_QUOTES) ?>" alt="" class="w-full sm:w-48 h-64 shrink-0 object-cover rounded-lg">
            <?php else: ?>
                <div class="w-full sm:w-48 h-64 shrink-0 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-sm">Poster</div>
            <?php endif; ?>
            <div>
                <h2 class="text-xl font-semibold mb-1"><?= htmlspecialchars($movie['title'], ENT_QUOTES) ?></h2>
                <p class="text-gray-500 text-sm mb-3">
                    <?php
                    $meta = array_filter([
                        $movie['release_year'],
                        implode(', ', $movie['genres']),
                        $movie['duration_minutes'] ? $movie['duration_minutes'] . ' min' : null,
                    ]);
                    echo htmlspecialchars(implode(' · ', $meta), ENT_QUOTES);
                    ?>
                </p>
                <p class="text-gray-400 mb-4"><?= nl2br(htmlspecialchars($movie['description'] ?? '', ENT_QUOTES)) ?></p>
                <dl class="text-sm space-y-1">
                    <?php if ($movie['director']): ?>
                        <div class="flex gap-2">
                            <dt class="text-gray-500">Director</dt>
                            <dd class="text-gray-300"><?= htmlspecialchars($movie['director'], ENT_QUOTES) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($movie['writers'])): ?>
                        <div class="flex gap-2">
                            <dt class="text-gray-500">Writers</dt>
                            <dd class="text-gray-300"><?= htmlspecialchars(implode(', ', $movie['writers']), ENT_QUOTES) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($movie['cast'])): ?>
                        <div class="flex gap-2">
                            <dt class="text-gray-500">Cast</dt>
                            <dd class="text-gray-300"><?= htmlspecialchars(implode(', ', $movie['cast']), ENT_QUOTES) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">Showings</h2>
            <div class="space-y-3 max-w-lg">
                <?php if (empty($showings)): ?>
                    <p class="text-gray-500 text-sm">No upcoming showings scheduled.</p>
                <?php endif; ?>
                <?php foreach ($showings as $showing): ?>
                    <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                        <div>
                            <p class="font-semibold"><?= htmlspecialchars(showingDayLabel($showing['show_datetime']), ENT_QUOTES) ?>, <?= htmlspecialchars(date('H:i', strtotime($showing['show_datetime'])), ENT_QUOTES) ?></p>
                            <p class="text-gray-400 text-sm"><?= htmlspecialchars($showing['screen_name'], ENT_QUOTES) ?></p>
                        </div>
                        <a href="#" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Book</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../src/components/footer.php'; ?>
</body>
</html>
