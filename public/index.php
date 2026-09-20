<?php

require __DIR__ . '/../models/Showing.php';
require __DIR__ . '/../demo/AutoSchedule.php';

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

// Matching border/text color classes for the screen badge (amber for film prints, blue for IMAX).
function screenTypeBadgeClasses(string $type): string
{
    return match ($type) {
        '35mm' => 'border-amber-500 text-amber-300',
        '70mm' => 'border-amber-400 text-amber-200',
        'imax' => 'border-blue-500 text-blue-300',
        default => 'border-gray-600 text-gray-300',
    };
}

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
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../src/components/public-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section class="py-8 min-h-screen">
            <h2 class="text-xl font-semibold mb-6">Tonight's Program</h2>

            <div class="mb-10">
                <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">Today's Program</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if (empty($todayMovies)): ?>
                        <p class="text-gray-500 text-sm">No showings scheduled for today yet.</p>
                    <?php endif; ?>
                    <?php foreach ($todayMovies as $movie): ?>
                        <div class="bg-gray-900 rounded-lg p-4">
                            <div class="flex gap-4 items-center">
                                <img src="<?= htmlspecialchars($movie['poster_url'] ?: 'assets/images/movie.jpg', ENT_QUOTES) ?>" alt="<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?> poster" class="w-1/2 aspect-2/3 object-cover rounded-lg shrink-0">
                                <div class="flex-1">
                                    <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border <?= htmlspecialchars(screenTypeBadgeClasses($movie['screen_type']), ENT_QUOTES) ?> mb-2"><?= htmlspecialchars($movie['screen_name'], ENT_QUOTES) ?></span>
                                    <h3 class="font-semibold mb-2"><a href="movie-details.php?id=<?= (int) $movie['movie_id'] ?>" class="hover:underline"><?= htmlspecialchars($movie['title'], ENT_QUOTES) ?></a></h3>
                                    <p class="text-gray-400 text-base"><?= htmlspecialchars(implode(' · ', array_filter([$movie['genres'], $movie['duration_minutes'] ? $movie['duration_minutes'] . ' min' : null])), ENT_QUOTES) ?></p>
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <?php foreach ($movie['times'] as $time): ?>
                                            <a href="#" class="text-xs bg-gray-800 hover:bg-blue-900 rounded px-2 py-1"><?= htmlspecialchars($time, ENT_QUOTES) ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="screens.php" class="inline-block text-blue-400 text-sm hover:underline mt-4">See the full schedule &rarr;</a>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">Later This Week</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php if (empty($laterThisWeek)): ?>
                        <p class="text-gray-500 text-sm">Nothing else scheduled yet.</p>
                    <?php endif; ?>
                    <?php foreach ($laterThisWeek as $movie): ?>
                        <div class="bg-gray-900 rounded-lg p-4">
                            <div class="flex gap-4 items-center">
                                <img src="<?= htmlspecialchars($movie['poster_url'] ?: 'assets/images/movie.jpg', ENT_QUOTES) ?>" alt="<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?> poster" class="w-1/2 aspect-2/3 object-cover rounded-lg shrink-0">
                                <div class="flex-1">
                                    <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border <?= htmlspecialchars(screenTypeBadgeClasses($movie['screen_type']), ENT_QUOTES) ?> mb-2"><?= htmlspecialchars($movie['screen_name'], ENT_QUOTES) ?></span>
                                    <h3 class="font-semibold mb-2"><a href="movie-details.php?id=<?= (int) $movie['movie_id'] ?>" class="hover:underline"><?= htmlspecialchars($movie['title'], ENT_QUOTES) ?></a></h3>
                                    <p class="text-gray-400 text-base"><?= htmlspecialchars(implode(' · ', array_filter([$movie['genres'], $movie['duration_minutes'] ? $movie['duration_minutes'] . ' min' : null])), ENT_QUOTES) ?></p>
                                    <p class="text-gray-500 text-sm mt-1">Next: <?= htmlspecialchars(showingDayLabel($movie['next_show_datetime']), ENT_QUOTES) ?>, <?= htmlspecialchars(date('g:i A', strtotime($movie['next_show_datetime'])), ENT_QUOTES) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="screens.php" class="inline-block text-blue-400 text-sm hover:underline mt-4">See the full schedule &rarr;</a>
            </div>
        </section>

        <section class="py-8 min-h-screen">
            <h2 class="text-xl font-semibold mb-4">About DWP Multiplex Cinema</h2>
            <p class="text-gray-400">Don't come. We hate people.</p>
        </section>

        <section class="py-8 min-h-screen">
            <h2 class="text-xl font-semibold mb-4">News</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">September 15, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder A</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=1" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">September 10, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder B</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=2" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">September 4, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder C</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=3" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">August 28, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder D</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=4" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">August 21, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder E</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=5" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">August 14, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder F</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=6" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
            </div>
            <a href="news.php" class="text-blue-400 text-sm hover:underline">See all news &rarr;</a>
        </section>
    </main>

    <?php require __DIR__ . '/../src/components/footer.php'; ?>
</body>
</html>
