<?php
require __DIR__ . '/../../models/News.php';

const NEWS_UPLOAD_DIR = __DIR__ . '/../assets/uploads/news';
const NEWS_UPLOAD_URL_PREFIX = 'assets/uploads/news';
const NEWS_IMAGE_MAX_BYTES = 2 * 1024 * 1024; // matches PHP's default upload_max_filesize (2M)
const NEWS_IMAGE_ALLOWED_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

// Ignores a missing file (keeps $currentPath), rejects anything that isn't actually an image
// (checked by content, not by the client-supplied name/type), and never trusts the original filename.
function handleNewsImageUpload(array $file, string $currentPath): string
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return $currentPath;
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('Image is too large (max 2MB).');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }

    if ($file['size'] > NEWS_IMAGE_MAX_BYTES) {
        throw new RuntimeException('Image is too large (max 2MB).');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset(NEWS_IMAGE_ALLOWED_TYPES[$mime])) {
        throw new RuntimeException('Image must be a JPEG, PNG, or WEBP.');
    }

    if (!is_dir(NEWS_UPLOAD_DIR)) {
        mkdir(NEWS_UPLOAD_DIR, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . NEWS_IMAGE_ALLOWED_TYPES[$mime];
    if (!move_uploaded_file($file['tmp_name'], NEWS_UPLOAD_DIR . '/' . $filename)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }

    return NEWS_UPLOAD_URL_PREFIX . '/' . $filename;
}

// Only deletes files inside our own managed upload folder, never an arbitrary path.
function deleteNewsImageIfManaged(?string $imagePath): void
{
    if ($imagePath && str_starts_with($imagePath, NEWS_UPLOAD_URL_PREFIX . '/')) {
        $fullPath = __DIR__ . '/../' . $imagePath;
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}

$saveError = null;
$editor = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    try {
        $currentImagePath = trim($_POST['current_image_path'] ?? '');
        $imagePath = handleNewsImageUpload($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE], $currentImagePath);

        if ($imagePath !== $currentImagePath) {
            deleteNewsImageIfManaged($currentImagePath);
        }

        News::save([
            'id' => $_POST['id'] !== '' ? (int) $_POST['id'] : null,
            'title' => trim($_POST['title'] ?? ''),
            'body' => trim($_POST['body'] ?? ''),
            'image_path' => $imagePath,
            'published_at' => $_POST['published_at'] ?? '',
        ]);
        header('Location: news.php?saved=' . urlencode(trim($_POST['title'] ?? '')));
        exit;
    } catch (Throwable $e) {
        $saveError = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $existing = News::findForAdmin((int) $_POST['id']);
    News::delete((int) $_POST['id']);
    if ($existing) {
        deleteNewsImageIfManaged($existing['image_path']);
    }
    header('Location: news.php?deleted=1');
    exit;
}

if (isset($_GET['edit_id'])) {
    $post = News::findForAdmin((int) $_GET['edit_id']);
    if ($post) {
        $editor = [
            'id' => $post['id'],
            'title' => $post['title'],
            'body' => $post['body'],
            'image_path' => $post['image_path'],
            'published_at' => date('Y-m-d', strtotime($post['published_at'])),
        ];
    }
}

if ($editor === null) {
    $editor = [
        'id' => null,
        'title' => '',
        'body' => '',
        'image_path' => '',
        // Defaults to today, so leaving it untouched publishes immediately; only needs changing to schedule for later.
        'published_at' => date('Y-m-d'),
    ];
}

$posts = News::allForAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - Admin - Untitled Cinema</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/nav-footer.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title">News Posts</h2>

            <?php
            if (isset($_GET['saved'])) {
                $snackbarType = 'success';
                $snackbarMessage = $_GET['saved'] . ' saved.';
            } elseif (isset($_GET['deleted'])) {
                $snackbarType = 'success';
                $snackbarMessage = 'News post deleted.';
            } elseif ($saveError) {
                $snackbarType = 'error';
                $snackbarMessage = 'Save failed: ' . $saveError;
            }
            require __DIR__ . '/../../components/snackbar.php';
            ?>

            <div class="movie-manager">
                <div>
                    <h3 class="admin-subheading">Add / Edit</h3>
                    <form method="post" enctype="multipart/form-data" class="movie-editor">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $editor['id'], ENT_QUOTES) ?>">
                        <input type="hidden" name="current_image_path" value="<?= htmlspecialchars($editor['image_path'], ENT_QUOTES) ?>">

                        <div class="form-field">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($editor['title'], ENT_QUOTES) ?>" required class="form-input">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Published At</label>
                            <input type="date" name="published_at" value="<?= htmlspecialchars($editor['published_at'], ENT_QUOTES) ?>" required class="form-input">
                        </div>
                        <?php if ($editor['image_path']): ?>
                            <img src="../<?= htmlspecialchars($editor['image_path'], ENT_QUOTES) ?>" alt="" class="movie-editor__poster">
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
