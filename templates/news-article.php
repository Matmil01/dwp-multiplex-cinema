<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article->title, ENT_QUOTES) ?> - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
    <link rel="stylesheet" href="/assets/css/showings-news.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <article class="news-article">
            <h2 class="news-article__title"><?= htmlspecialchars($article->title, ENT_QUOTES) ?></h2>
            <p class="news-card__date"><?= $article->publishedLabel() ?></p>
            <?php if ($article->image_path): ?>
                <img src="/<?= htmlspecialchars($article->image_path, ENT_QUOTES) ?>" alt="" class="news-article__thumbnail">
            <?php else: ?>
                <div class="news-article__thumbnail">Article image coming soon</div>
            <?php endif; ?>
            <p class="news-article__body"><?= nl2br(htmlspecialchars($article->body, ENT_QUOTES)) ?></p>
        </article>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
