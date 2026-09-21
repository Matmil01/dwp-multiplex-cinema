<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - Admin - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/nav-footer.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="site-body">
    <header class="site-header">
        <h1 class="site-title">Admin</h1>
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <div class="page-heading">
                <h2 class="section-title">News Posts</h2>
                <a href="#" class="button">Add Post</a>
            </div>
            <div class="admin-list">
                <div class="admin-list-item">
                    <div>
                        <p class="admin-list-item__title">Headline placeholder A</p>
                        <p class="admin-list-item__details">September 15, 2026</p>
                    </div>
                    <div class="admin-list-item__actions">
                        <a href="#" class="inline-link">Edit</a>
                        <a href="#" class="danger-link">Delete</a>
                    </div>
                </div>
                <div class="admin-list-item">
                    <div>
                        <p class="admin-list-item__title">Headline placeholder B</p>
                        <p class="admin-list-item__details">September 10, 2026</p>
                    </div>
                    <div class="admin-list-item__actions">
                        <a href="#" class="inline-link">Edit</a>
                        <a href="#" class="danger-link">Delete</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
