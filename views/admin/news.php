<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - Admin - Untitled Cinema</title>
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
            <h2 class="section-title">News Posts</h2>

            <?php require __DIR__ . '/../../components/snackbar.php'; ?>

            <div class="movie-manager">
                <div>
                    <h3 class="admin-subheading">Add / Edit</h3>
                    <form method="post" enctype="multipart/form-data" class="movie-editor">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $editor['id'], ENT_QUOTES) ?>">

                        <div class="form-field">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($editor['title'], ENT_QUOTES) ?>" required class="form-input">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Published At</label>
                            <input type="date" name="published_at" value="<?= htmlspecialchars($editor['published_at'], ENT_QUOTES) ?>" required class="form-input">
                        </div>
                        <?php if ($editor['image_path']): ?>
                            <img src="/<?= htmlspecialchars($editor['image_path'], ENT_QUOTES) ?>" alt="" class="movie-editor__poster">
                        <?php endif; ?>
                        <div class="form-field">
                            <label class="form-label">Image (JPEG/PNG/WEBP, max 2MB)</label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="form-input">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Body</label>
                            <textarea name="body" rows="6" required class="form-input"><?= htmlspecialchars($editor['body'], ENT_QUOTES) ?></textarea>
                        </div>
                        <button type="submit" class="base-button">Save Post</button>
                    </form>
                </div>

                <div>
                    <h3 class="admin-subheading">Posts</h3>
                    <div class="admin-list">
                        <?php if (empty($posts)): ?>
                            <p class="empty-message">No news posts yet.</p>
                        <?php endif; ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="admin-list-item">
                                <div>
                                    <p class="admin-list-item__title"><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></p>
                                    <p class="admin-list-item__details">
                                        <?= date('F j, Y', strtotime($post['published_at'])) ?>
                                        <?= strtotime($post['published_at']) > strtotime('today') ? ' (scheduled)' : '' ?>
                                    </p>
                                </div>
                                <div class="admin-list-item__actions">
                                    <a href="?edit_id=<?= (int) $post['id'] ?>" class="inline-link">Edit</a>
                                    <form method="post" onsubmit="return confirm('Delete this news post?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
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

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
