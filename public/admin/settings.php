<?php

require __DIR__ . '/../../models/CompanyInfo.php';

$saveError = null;
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        CompanyInfo::save([
            'description' => $_POST['description'],
            'opening_time' => $_POST['opening_time'],
            'closing_time' => $_POST['closing_time'],
            'address' => $_POST['address'],
            'phone' => $_POST['phone'],
        ]);
        $saved = true;
    } catch (Throwable $e) {
        $saveError = $e->getMessage();
    }
}

$company = CompanyInfo::find();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin - Untitled Cinema</title>
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
            <div class="page-heading">
                <h2 class="section-title">Staff Accounts</h2>
                <a href="#" class="base-button">Invite Staff</a>
            </div>
            <div class="admin-list">
                <div class="admin-list-item">
                    <p class="admin-list-item__title">Placeholder Staff Member</p>
                    <a href="#" class="danger-link">Remove</a>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
