<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies - Admin - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title">Movies</h2>

            <?php require __DIR__ . '/../../components/snackbar.php'; ?>

            <div class="movie-manager">
                <div>
                    <h3 class="admin-subheading">Add / Edit</h3>

                    <form method="get" class="search-form">
                        <input type="text" name="q" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES) ?>" placeholder="Search TMDB by title..." class="form-input">
                        <button type="submit" class="base-button">Search</button>
                    </form>

                    <?php if ($searchError): ?>
                        <p class="notice notice--error"><?= htmlspecialchars($searchError, ENT_QUOTES) ?></p>
                    <?php elseif ($searchQuery !== ''): ?>
                        <div class="search-results">
                            <?php if (empty($searchResults)): ?>
                                <p class="empty-message">No results for "<?= htmlspecialchars($searchQuery, ENT_QUOTES) ?>".</p>
                            <?php endif; ?>
                            <?php foreach ($searchResults as $result): ?>
                                <a href="?tmdb_id=<?= (int) $result['id'] ?>" class="movie-result">
                                    <?php if ($result['posterUrl']): ?>
                                        <img src="<?= htmlspecialchars($result['posterUrl'], ENT_QUOTES) ?>" alt="" class="movie-result__poster">
                                    <?php endif; ?>
                                    <span class="movie-result__title"><?= htmlspecialchars($result['title'], ENT_QUOTES) ?> <span class="movie-result__year">(<?= htmlspecialchars($result['year'], ENT_QUOTES) ?>)</span></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="movie-editor">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $editor['id'], ENT_QUOTES) ?>">
                        <input type="hidden" name="tmdb_id" value="<?= htmlspecialchars((string) $editor['tmdb_id'], ENT_QUOTES) ?>">

                        <?php if ($editor['poster_url']): ?>
                            <img src="<?= htmlspecialchars($editor['poster_url'], ENT_QUOTES) ?>" alt="" class="movie-editor__poster">
                            <input type="hidden" name="poster_url" value="<?= htmlspecialchars($editor['poster_url'], ENT_QUOTES) ?>">
                        <?php else: ?>
                            <input type="hidden" name="poster_url" value="">
                        <?php endif; ?>

                        <div class="form-field">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($editor['title'], ENT_QUOTES) ?>" required class="form-input">
                        </div>
                        <div class="form-field-row">
                            <div class="form-field">
                                <label class="form-label">Release Year</label>
                                <input type="number" name="release_year" min="1888" max="2100" value="<?= htmlspecialchars((string) ($editor['release_year'] ?? ''), ENT_QUOTES) ?>" class="form-input">
                            </div>
                            <div class="form-field">
                                <label class="form-label">Runtime (min)</label>
                                <input type="number" name="duration_minutes" value="<?= htmlspecialchars((string) ($editor['duration_minutes'] ?? ''), ENT_QUOTES) ?>" class="form-input">
                            </div>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Genres (comma-separated)</label>
                            <input type="text" name="genres" value="<?= htmlspecialchars($editor['genres'], ENT_QUOTES) ?>" class="form-input">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Synopsis</label>
                            <textarea name="description" rows="3" class="form-input"><?= htmlspecialchars($editor['description'], ENT_QUOTES) ?></textarea>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Director</label>
                            <input type="text" name="director" value="<?= htmlspecialchars($editor['director'], ENT_QUOTES) ?>" class="form-input">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Writers (comma-separated)</label>
                            <input type="text" name="writers" value="<?= htmlspecialchars($editor['writers'], ENT_QUOTES) ?>" class="form-input">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Cast (comma-separated)</label>
                            <input type="text" name="cast" value="<?= htmlspecialchars($editor['cast'], ENT_QUOTES) ?>" class="form-input">
                        </div>
                        <button type="submit" class="base-button">Save Movie</button>
                    </form>
                </div>

                <div>
                    <div class="catalog-heading">
                        <h3 class="admin-subheading">Catalog</h3>
                        <input type="text" id="catalogFilter" placeholder="Filter catalog..." class="form-input catalog-heading__filter">
                    </div>
                    <div id="catalogList" class="catalog-list">
                        <?php if (empty($catalog)): ?>
                            <p class="empty-message">No movies yet &mdash; search TMDB to add one.</p>
                        <?php endif; ?>
                        <?php foreach ($catalog as $movie): ?>
                            <div data-title="<?= htmlspecialchars(strtolower($movie['title']), ENT_QUOTES) ?>" class="catalog-item">
                                <?php if ($movie['poster_url']): ?>
                                    <img src="<?= htmlspecialchars($movie['poster_url'], ENT_QUOTES) ?>" alt="" class="movie-result__poster">
                                <?php endif; ?>
                                <div class="catalog-item__details">
                                    <p class="catalog-item__title"><?= htmlspecialchars($movie['title'], ENT_QUOTES) ?></p>
                                    <p class="catalog-item__metadata">
                                        <?php
                                        $meta = array_filter([
                                            $movie['release_year'],
                                            implode(', ', $movie['genres']),
                                            $movie['duration_minutes'] ? $movie['duration_minutes'] . ' min' : null,
                                        ]);
                                        echo htmlspecialchars(implode(' · ', $meta), ENT_QUOTES);
                                        ?>
                                    </p>
                                </div>
                                <div class="catalog-item__actions">
                                    <a href="?edit_id=<?= (int) $movie['id'] ?>" class="inline-link">Edit</a>
                                    <form method="post" onsubmit="return confirm('Delete this movie?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $movie['id'] ?>">
                                        <button type="submit" class="danger-button">Delete</button>
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
