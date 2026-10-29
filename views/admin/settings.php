<?php require __DIR__ . '/../../components/screen-format.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin - Untitled Cinema</title>
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
        <?php
        if ($saved) {
            $snackbarType = 'success';
            $snackbarMessage = 'Changes saved.';
        } elseif ($saveError) {
            $snackbarType = 'error';
            $snackbarMessage = $saveError;
        }
        require __DIR__ . '/../../components/snackbar.php';
        ?>

        <section>
            <h2 class="section-title">Company Info</h2>
            <form method="post" action="" class="form-stack">
                <div class="form-field">
                    <label for="tagline" class="form-label">Tagline</label>
                    <input type="text" id="tagline" name="tagline" maxlength="150" value="<?= htmlspecialchars($company['tagline'] ?? '', ENT_QUOTES) ?>" required class="form-input">
                </div>
                <div class="form-field">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="4" class="form-input"><?= htmlspecialchars($company['description'] ?? '', ENT_QUOTES) ?></textarea>
                </div>
                <div class="form-field">
                    <label for="opening_time" class="form-label">Opening Time</label>
                    <input type="time" id="opening_time" name="opening_time" value="<?= htmlspecialchars(substr($company['opening_time'] ?? '', 0, 5), ENT_QUOTES) ?>" required class="form-input">
                </div>
                <div class="form-field">
                    <label for="closing_time" class="form-label">Closing Time</label>
                    <input type="time" id="closing_time" name="closing_time" value="<?= htmlspecialchars(substr($company['closing_time'] ?? '', 0, 5), ENT_QUOTES) ?>" required class="form-input">
                </div>
                <div class="form-field">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($company['address'] ?? '', ENT_QUOTES) ?>" class="form-input">
                </div>
                <div class="form-field">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($company['phone'] ?? '', ENT_QUOTES) ?>" class="form-input">
                </div>
                <button type="submit" class="base-button">Save Changes</button>
            </form>
        </section>

        <section>
            <h2 class="section-title">Ticket Prices</h2>
            <form method="post" action="/admin/settings/prices" class="form-stack">
                <?php foreach ($screens as $screen): ?>
                    <div class="form-field">
                        <label for="price-<?= (int) $screen['id'] ?>" class="form-label"><?= htmlspecialchars(screenDisplayName($screen['type']), ENT_QUOTES) ?> (kr. per seat)</label>
                        <input type="number" id="price-<?= (int) $screen['id'] ?>" name="prices[<?= (int) $screen['id'] ?>]" min="<?= Screen::MIN_PRICE ?>" max="<?= Screen::MAX_PRICE ?>" step="1" value="<?= htmlspecialchars((string) $screen['ticket_price'], ENT_QUOTES) ?>" required class="form-input">
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="base-button">Save Prices</button>
            </form>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
