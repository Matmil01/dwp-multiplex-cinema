<?php

class AdminNewsController
{
    // Where files go on disk, and the matching path prefix stored in the database / used in <img src>.
    private const UPLOAD_DIR = __DIR__ . '/../public/assets/uploads/news';
    private const UPLOAD_URL_PREFIX = 'assets/uploads/news';
    private const IMAGE_MAX_BYTES = 2 * 1024 * 1024; // matches PHP's default upload_max_filesize (2M)
    // Detected content type => extension WE assign. Anything not in this list is refused.
    private const IMAGE_ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function index(array $query): void
    {
        $this->showPage($query, null, null);
    }

    // $files is $_FILES: PHP's list of uploaded files. Only present because the form has
    // enctype="multipart/form-data", which is what lets a form carry a file.
    public function handlePost(array $post, array $files, array $query): void
    {
        $action = $post['action'] ?? '';

        if ($action === 'save') {
            $this->save($post, $files);
        } elseif ($action === 'delete') {
            $this->delete((int) ($post['id'] ?? 0));
        } else {
            $this->index($query);
        }
    }

    public function save(array $input, array $files): void
    {
        $postId = null;
        $currentImagePath = '';

        try {
            // Read the old image path from the saved article, not from a form field that can be changed.
            $postId = ($input['id'] ?? '') !== '' ? (int) $input['id'] : null;
            $existing = $postId !== null ? News::findForAdmin($postId) : null;
            if ($postId !== null && !$existing) {
                throw new RuntimeException('News post not found.');
            }
            $currentImagePath = $existing['image_path'] ?? '';
            // $files['image'] is the uploaded file's info (name, size, tmp_name, error...). If no
            // file was chosen, pass a stand-in that says so, and the old image path is kept.
            $imagePath = $this->handleImageUpload($files['image'] ?? ['error' => UPLOAD_ERR_NO_FILE], $currentImagePath);

            // Keep the old image on disk while saving. If this fails, the article still has its image.
            News::save([
                'id' => $postId,
                'title' => trim($input['title'] ?? ''),
                'body' => trim($input['body'] ?? ''),
                'image_path' => $imagePath,
                'published_at' => $input['published_at'] ?? '',
            ]);

            // The article now points to the new image, so the old one can be removed safely.
            if ($imagePath !== $currentImagePath) {
                $this->deleteImageIfManaged($currentImagePath);
            }
            header('Location: /admin/news?saved=' . urlencode(trim($input['title'] ?? '')));
            exit;
        } catch (Throwable $e) {
            $saveError = $e->getMessage();
        }

        // Saving failed: keep the submitted text and date so you can correct the problem and retry.
        $editor = [
            'id' => $postId,
            'title' => $input['title'] ?? '',
            'body' => $input['body'] ?? '',
            // Show the existing saved image. Browsers do not let us refill the file picker.
            'image_path' => $currentImagePath,
            'published_at' => $input['published_at'] ?? '',
        ];

        $this->showPage([], $saveError, $editor);
    }

    public function delete(int $id): void
    {
        // Look the article up first so we still know its image path after the row is gone.
        $existing = News::findForAdmin($id);
        News::delete($id);
        if ($existing) {
            $this->deleteImageIfManaged($existing['image_path']);
        }
        header('Location: /admin/news?deleted=1');
        exit;
    }

    private function showPage(array $query, ?string $saveError, ?array $editor): void
    {
        if ($editor === null && isset($query['edit_id'])) {
            $post = News::findForAdmin((int) $query['edit_id']);
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

        if ($saveError) {
            $snackbarType = 'error';
            $snackbarMessage = 'Save failed: ' . $saveError;
        } elseif (isset($query['saved'])) {
            $snackbarType = 'success';
            $snackbarMessage = $query['saved'] . ' saved.';
        } elseif (isset($query['deleted'])) {
            $snackbarType = 'success';
            $snackbarMessage = 'News post deleted.';
        }

        require __DIR__ . '/../views/admin/news.php';
    }

    // Ignores a missing file (keeps $currentPath), rejects anything that isn't actually an image
    // (checked by content, not by the client-supplied name/type), and never trusts the original filename.
    // Returns the path text to store in the database.
    private function handleImageUpload(array $file, string $currentPath): string
    {
        // PHP reports what happened with the upload in $file['error']. UPLOAD_ERR_OK = fine,
        // UPLOAD_ERR_NO_FILE = the picker was left empty, the *_SIZE ones = too big.
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentPath;
        }

        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            throw new RuntimeException('Image is too large (max 2MB).');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed.');
        }

        if ($file['size'] > self::IMAGE_MAX_BYTES) {
            throw new RuntimeException('Image is too large (max 2MB).');
        }

        // finfo reads the first bytes of the file itself to work out what it really is
        // ("image/jpeg" etc.). The browser also sends a type in $file['type'], but that is
        // just a claim the uploader controls, so it is ignored here.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);

        if (!isset(self::IMAGE_ALLOWED_TYPES[$mime])) {
            throw new RuntimeException('Image must be a JPEG, PNG, or WEBP.');
        }

        // Create the upload folder if it doesn't exist yet (true = create parent folders too).
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        // random_bytes(16) -> 16 random bytes; bin2hex -> 32 random hex characters. The
        // extension comes from the detected type, so "evil.php" can only ever be saved as
        // "<random>.jpg". move_uploaded_file() moves it from PHP's temp folder into ours.
        $filename = bin2hex(random_bytes(16)) . '.' . self::IMAGE_ALLOWED_TYPES[$mime];
        if (!move_uploaded_file($file['tmp_name'], self::UPLOAD_DIR . '/' . $filename)) {
            throw new RuntimeException('Could not save the uploaded image.');
        }

        return self::UPLOAD_URL_PREFIX . '/' . $filename;
    }

    // Only deletes files inside our own managed upload folder, never an arbitrary path.
    // (Without the str_starts_with check, a tampered path like "../../config/config.php"
    // could make this delete something important.) unlink() = delete a file.
    private function deleteImageIfManaged(?string $imagePath): void
    {
        if ($imagePath && str_starts_with($imagePath, self::UPLOAD_URL_PREFIX . '/')) {
            $fullPath = __DIR__ . '/../public/' . $imagePath;
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
