<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Headline placeholder - Untitled Cinema</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/nav-footer.css">
    <link rel="stylesheet" href="assets/css/showings-news.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <article class="news-article">
            <div class="news-article__thumbnail">Article image coming soon</div>
            <p class="news-card__date">September 15, 2026</p>
            <h2 class="news-article__title">Headline placeholder A</h2>
            <p class="news-article__body">
                Full post body placeholder text goes here. This is where the entire news
                post content will render once it's pulled from the database instead of
                being hardcoded like this.
            </p>
        </article>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
