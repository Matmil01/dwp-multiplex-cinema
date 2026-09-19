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
        <?php require __DIR__ . '/../src/components/navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-4">Movies</h2>

            <div class="flex gap-3 mb-6">
                <button class="bg-gray-800 rounded-lg px-4 py-2 text-sm">Now Showing</button>
                <button class="bg-gray-900 rounded-lg px-4 py-2 text-sm text-gray-400">Not Yet Scheduled</button>
                <button class="bg-gray-900 rounded-lg px-4 py-2 text-sm text-gray-400">Previously Shown</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="movie-details.php?id=1" class="block bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-40 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Poster</div>
                    <h3 class="font-semibold">Movie placeholder A</h3>
                    <p class="text-gray-400 text-sm">Drama, Mystery &middot; 118 min &middot; PG-13</p>
                </a>
                <a href="movie-details.php?id=2" class="block bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-40 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Poster</div>
                    <h3 class="font-semibold">Movie placeholder B</h3>
                    <p class="text-gray-400 text-sm">Action, Thriller &middot; 102 min &middot; R</p>
                </a>
                <a href="movie-details.php?id=3" class="block bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-40 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Poster</div>
                    <h3 class="font-semibold">Movie placeholder C</h3>
                    <p class="text-gray-400 text-sm">Comedy &middot; 95 min &middot; PG</p>
                </a>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../src/components/footer.php'; ?>
</body>
</html>
