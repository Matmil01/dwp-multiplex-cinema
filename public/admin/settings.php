<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/nav-footer.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="site-body">
    <header class="site-header">
        <h1 class="site-title">Admin</h1>
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title">Company Info</h2>
            <form method="post" action="#" class="form-stack">
                <div class="form-field">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="4" class="form-input"></textarea>
                </div>
                <div class="form-field">
                    <label for="opening_hours" class="form-label">Opening Hours</label>
                    <input type="text" id="opening_hours" name="opening_hours" class="form-input">
                </div>
                <div class="form-field">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" id="address" name="address" class="form-input">
                </div>
                <div class="form-field">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-input">
                </div>
                <button type="submit" class="button">Save Changes</button>
            </form>
        </section>

        <section>
            <div class="page-heading">
                <h2 class="section-title">Staff Accounts</h2>
                <a href="#" class="button">Invite Staff</a>
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
