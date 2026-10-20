<?php

class AdminNewsController
{
    private const UPLOAD_DIR = __DIR__ . '/../public/assets/uploads/news';
    private const UPLOAD_URL_PREFIX = 'assets/uploads/news';
    private const IMAGE_MAX_BYTES = 2 * 1024 * 1024; // matches PHP's default upload_max_filesize (2M)
    private const IMAGE_ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function index(array $query): void
    {
        $this->showPage($query, null, null);
    }

    public function handlePost(array $post, array $files, array $query): void
    {
        $action = $post['action'] ?? '';

        if ($action === 'save') {
            $this->save($post, $files);
        } elseif ($action === 'delete') {
            $this->delete((int) $post['id']);
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
    private function handleImageUpload(array $file, string $currentPath): string
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

        if ($file['size'] > self::IMAGE_MAX_BYTES) {
            throw new RuntimeException('Image is too large (max 2MB).');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::IMAGE_ALLOWED_TYPES[$mime])) {
            throw new RuntimeException('Image must be a JPEG, PNG, or WEBP.');
        }

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . self::IMAGE_ALLOWED_TYPES[$mime];
        if (!move_uploaded_file($file['tmp_name'], self::UPLOAD_DIR . '/' . $filename)) {
            throw new RuntimeException('Could not save the uploaded image.');
        }

        return self::UPLOAD_URL_PREFIX . '/' . $filename;
    }

    // Only deletes files inside our own managed upload folder, never an arbitrary path.
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
