<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - Untitled Cinema</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/nav-footer.css">
    <link rel="stylesheet" href="assets/css/showings-news.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title section-title--primary">News</h2>

            <div class="news-list">
                <?php if (empty($articles)): ?>
                    <p class="empty-message">No news yet &mdash; check back soon.</p>
                <?php endif; ?>
                <?php foreach ($articles as $article): ?>
                    <article class="news-list-item">
                        <?php if ($article['image_path']): ?>
                            <img src="<?= htmlspecialchars($article['image_path'], ENT_QUOTES) ?>" alt="" class="news-list-item__thumbnail">
                        <?php else: ?>
                            <div class="news-list-item__thumbnail">Article image coming soon</div>
                        <?php endif; ?>
                        <div class="news-list-item__content">
                            <p class="news-card__date"><?= date('F j, Y', strtotime($article['published_at'])) ?></p>
                            <h3 class="news-card__title"><a href="news-article.php?id=<?= (int) $article['id'] ?>"><?= htmlspecialchars($article['title'], ENT_QUOTES) ?></a></h3>
                            <p class="news-card__excerpt"><?= htmlspecialchars(News::excerpt($article['body']), ENT_QUOTES) ?></p>
                            <a href="news-article.php?id=<?= (int) $article['id'] ?>" class="text-link">Read more &rarr;</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
