<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES) ?> - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title"><?= htmlspecialchars($title, ENT_QUOTES) ?></h2>
            <p class="muted-text"><?= htmlspecialchars($message, ENT_QUOTES) ?></p>
            <p><a href="/" class="inline-link">Back to the front page</a></p>
        </section>
    </main>
</body>
</html>
