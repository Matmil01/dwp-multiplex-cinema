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
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-4">Movies</h2>

            <div class="flex gap-3 mb-6">
                <a href="?status=all" class="rounded-lg px-4 py-2 text-sm <?= $status === 'all' ? 'bg-gray-800' : 'bg-gray-900 text-gray-400' ?>">All Movies</a>
                <a href="?status=now_showing" class="rounded-lg px-4 py-2 text-sm <?= $status === 'now_showing' ? 'bg-gray-800' : 'bg-gray-900 text-gray-400' ?>">Now Showing</a>
                <a href="?status=not_scheduled" class="rounded-lg px-4 py-2 text-sm <?= $status === 'not_scheduled' ? 'bg-gray-800' : 'bg-gray-900 text-gray-400' ?>">Not Yet Scheduled</a>
                <a href="?status=previously_shown" class="rounded-lg px-4 py-2 text-sm <?= $status === 'previously_shown' ? 'bg-gray-800' : 'bg-gray-900 text-gray-400' ?>">Previously Shown</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php if (empty($movies)): ?>
                    <p class="text-gray-500 text-sm">No movies in this category.</p>
                <?php endif; ?>
                <?php foreach ($movies as $movie): ?>
                    <a href="movie-details.php?id=<?= (int) $movie['id'] ?>" class="block bg-gray-900 rounded-lg p-4">
                        <?php if ($movie['poster_url']): ?>
                            <img src="<?= htmlspecialchars($movie['poster_url'], ENT_QUOTES) ?>" alt="" class="w-full aspect-2/3 object-cover rounded-lg mb-2">
                        <?php else: ?>
                            <div class="w-full aspect-2/3 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Poster</div>
                        <?php endif; ?>
                        <h3 class="font-semibold"><?= htmlspecialchars($movie['title'], ENT_QUOTES) ?></h3>
                        <p class="text-gray-400 text-sm">
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
