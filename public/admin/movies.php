<?php
require __DIR__ . '/../../classes/TmdbAdapter.php';
require __DIR__ . '/../../models/Movie.php';

$searchQuery = trim($_GET['q'] ?? '');
$searchResults = [];
$searchError = null;
$saveError = null;
$editor = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    try {
        Movie::save([
            'id' => $_POST['id'] !== '' ? (int) $_POST['id'] : null,
            'tmdb_id' => $_POST['tmdb_id'] !== '' ? (int) $_POST['tmdb_id'] : null,
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'duration_minutes' => $_POST['duration_minutes'] !== '' ? (int) $_POST['duration_minutes'] : null,
            'poster_url' => trim($_POST['poster_url'] ?? ''),
            'release_year' => $_POST['release_year'] !== '' ? (int) $_POST['release_year'] : null,
            'genres' => $_POST['genres'] ?? '',
            'director' => $_POST['director'] ?? '',
            'writers' => $_POST['writers'] ?? '',
            'cast' => $_POST['cast'] ?? '',
        ]);
        header('Location: movies.php?saved=' . urlencode(trim($_POST['title'] ?? '')));
        exit;
    } catch (Throwable $e) {
        $saveError = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    Movie::delete((int) $_POST['id']);
    header('Location: movies.php?deleted=1');
    exit;
}

if (isset($_GET['tmdb_id'])) {
    try {
        $details = (new TmdbAdapter())->getMovieDetails((int) $_GET['tmdb_id']);
        $editor = [
            'id' => null,
            'tmdb_id' => $details['tmdb_id'],
            'title' => $details['title'],
            'description' => $details['description'],
            'duration_minutes' => $details['duration_minutes'],
            'poster_url' => $details['poster_url'],
            'release_year' => $details['release_year'],
            'genres' => implode(', ', $details['genres']),
            'director' => $details['director']['name'] ?? '',
            'writers' => implode(', ', array_column($details['writers'], 'name')),
            'cast' => implode(', ', array_column($details['cast'], 'name')),
        ];
    } catch (RuntimeException $e) {
        $searchError = $e->getMessage();
    }
} elseif (isset($_GET['edit_id'])) {
    $movie = Movie::find((int) $_GET['edit_id']);
    if ($movie) {
        $editor = [
            'id' => $movie['id'],
            'tmdb_id' => $movie['tmdb_id'],
            'title' => $movie['title'],
            'description' => $movie['description'],
            'duration_minutes' => $movie['duration_minutes'],
            'poster_url' => $movie['poster_url'],
            'release_year' => $movie['release_year'],
            'genres' => implode(', ', $movie['genres']),
            'director' => $movie['director'] ?? '',
            'writers' => implode(', ', $movie['writers']),
            'cast' => implode(', ', $movie['cast']),
        ];
    }
}

if ($searchQuery !== '') {
    try {
        $searchResults = (new TmdbAdapter())->searchMovies($searchQuery);
    } catch (RuntimeException $e) {
        $searchError = $e->getMessage();
    }
}

$catalog = Movie::all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies - Admin - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="../assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">Admin</h1>
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-4">Movies</h2>

            <?php if (isset($_GET['saved'])): ?>
                <p class="text-green-400 text-sm mb-4"><?= htmlspecialchars($_GET['saved'], ENT_QUOTES) ?> saved.</p>
            <?php elseif (isset($_GET['deleted'])): ?>
                <p class="text-green-400 text-sm mb-4">Movie deleted.</p>
            <?php endif; ?>
            <?php if ($saveError): ?>
                <p class="text-red-400 text-sm mb-4">Save failed: <?= htmlspecialchars($saveError, ENT_QUOTES) ?></p>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-3">Add / Edit</h3>

                    <form method="get" class="flex gap-2 mb-4">
                        <input type="text" name="q" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES) ?>" placeholder="Search TMDB by title..." class="flex-1 bg-gray-900 border border-gray-800 rounded-lg px-3 py-2 text-sm">
                        <button type="submit" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Search</button>
                    </form>

                    <?php if ($searchError): ?>
                        <p class="text-red-400 text-sm mb-4"><?= htmlspecialchars($searchError, ENT_QUOTES) ?></p>
                    <?php elseif ($searchQuery !== ''): ?>
                        <div class="space-y-2 mb-6">
                            <?php if (empty($searchResults)): ?>
                                <p class="text-gray-500 text-sm">No results for "<?= htmlspecialchars($searchQuery, ENT_QUOTES) ?>".</p>
                            <?php endif; ?>
                            <?php foreach ($searchResults as $result): ?>
                                <a href="?tmdb_id=<?= (int) $result['id'] ?>" class="bg-gray-900 rounded-lg p-3 flex items-center gap-3 hover:bg-gray-800">
                                    <?php if ($result['posterUrl']): ?>
                                        <img src="<?= htmlspecialchars($result['posterUrl'], ENT_QUOTES) ?>" alt="" class="w-10 h-15 object-cover rounded shrink-0">
                                    <?php endif; ?>
                                    <span class="text-sm"><?= htmlspecialchars($result['title'], ENT_QUOTES) ?> <span class="text-gray-500">(<?= htmlspecialchars($result['year'], ENT_QUOTES) ?>)</span></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($editor): ?>
                        <form method="post" class="bg-gray-900 rounded-lg p-4 space-y-3">
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $editor['id'], ENT_QUOTES) ?>">
                            <input type="hidden" name="tmdb_id" value="<?= htmlspecialchars((string) $editor['tmdb_id'], ENT_QUOTES) ?>">

                            <?php if ($editor['poster_url']): ?>
                                <img src="<?= htmlspecialchars($editor['poster_url'], ENT_QUOTES) ?>" alt="" class="w-20 rounded mb-2">
                                <input type="hidden" name="poster_url" value="<?= htmlspecialchars($editor['poster_url'], ENT_QUOTES) ?>">
                            <?php else: ?>
                                <input type="hidden" name="poster_url" value="">
                            <?php endif; ?>

                            <div>
                                <label class="text-gray-400 text-sm block mb-1">Title</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($editor['title'], ENT_QUOTES) ?>" required class="w-full bg-gray-800 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <label class="text-gray-400 text-sm block mb-1">Release Year</label>
                                    <input type="number" name="release_year" min="1888" max="2100" value="<?= htmlspecialchars((string) ($editor['release_year'] ?? ''), ENT_QUOTES) ?>" class="w-full bg-gray-800 rounded-lg px-3 py-2 text-sm">
                                </div>
                                <div class="flex-1">
                                    <label class="text-gray-400 text-sm block mb-1">Runtime (min)</label>
                                    <input type="number" name="duration_minutes" value="<?= htmlspecialchars((string) ($editor['duration_minutes'] ?? ''), ENT_QUOTES) ?>" class="w-full bg-gray-800 rounded-lg px-3 py-2 text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="text-gray-400 text-sm block mb-1">Genres (comma-separated)</label>
                                <input type="text" name="genres" value="<?= htmlspecialchars($editor['genres'], ENT_QUOTES) ?>" class="w-full bg-gray-800 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-gray-400 text-sm block mb-1">Synopsis</label>
                                <textarea name="description" rows="3" class="w-full bg-gray-800 rounded-lg px-3 py-2 text-sm"><?= htmlspecialchars($editor['description'], ENT_QUOTES) ?></textarea>
                            </div>
                            <div>
                                <label class="text-gray-400 text-sm block mb-1">Director</label>
                                <input type="text" name="director" value="<?= htmlspecialchars($editor['director'], ENT_QUOTES) ?>" class="w-full bg-gray-800 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-gray-400 text-sm block mb-1">Writers (comma-separated)</label>
                                <input type="text" name="writers" value="<?= htmlspecialchars($editor['writers'], ENT_QUOTES) ?>" class="w-full bg-gray-800 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-gray-400 text-sm block mb-1">Cast (comma-separated)</label>
                                <input type="text" name="cast" value="<?= htmlspecialchars($editor['cast'], ENT_QUOTES) ?>" class="w-full bg-gray-800 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <button type="submit" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Save Movie</button>
                        </form>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">Search TMDB above, or click Edit on a catalog entry, to fill out this form.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400">Catalog</h3>
                        <input type="text" id="catalogFilter" placeholder="Filter catalog..." class="bg-gray-900 border border-gray-800 rounded-lg px-3 py-1.5 text-sm">
                    </div>
                    <div id="catalogList" class="space-y-2">
                        <?php if (empty($catalog)): ?>
                            <p class="text-gray-500 text-sm">No movies yet &mdash; search TMDB to add one.</p>
                        <?php endif; ?>
                        <?php foreach ($catalog as $movie): ?>
                            <div data-title="<?= htmlspecialchars(strtolower($movie['title']), ENT_QUOTES) ?>" class="bg-gray-900 rounded-lg p-3 flex items-center gap-3">
                                <?php if ($movie['poster_url']): ?>
                                    <img src="<?= htmlspecialchars($movie['poster_url'], ENT_QUOTES) ?>" alt="" class="w-10 h-15 object-cover rounded shrink-0">
                                <?php endif; ?>
                                <div class="flex-1">
                                    <p class="font-semibold text-sm"><?= htmlspecialchars($movie['title'], ENT_QUOTES) ?></p>
                                    <p class="text-gray-500 text-xs">
                                        <?php
                                        $meta = array_filter([
                                            implode(', ', $movie['genres']),
                                            $movie['duration_minutes'] ? $movie['duration_minutes'] . ' min' : null,
                                        ]);
                                        echo htmlspecialchars(implode(' · ', $meta), ENT_QUOTES);
                                        ?>
                                    </p>
                                </div>
                                <div class="flex gap-3 text-sm shrink-0">
                                    <a href="?edit_id=<?= (int) $movie['id'] ?>" class="text-blue-400 hover:underline">Edit</a>
                                    <form method="post" onsubmit="return confirm('Delete this movie?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $movie['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.getElementById('catalogFilter').addEventListener('input', function () {
            var term = this.value.toLowerCase();
            document.querySelectorAll('#catalogList [data-title]').forEach(function (row) {
                row.style.display = row.dataset.title.includes(term) ? '' : 'none';
            });
        });
    </script>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
