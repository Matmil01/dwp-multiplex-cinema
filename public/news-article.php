<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Headline placeholder - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/main.css">
</head>
<body class="site-body">
    <header class="site-header">
        <h1 class="site-title">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <article class="news-article">
            <div class="news-article__thumbnail">Thumbnail</div>
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
