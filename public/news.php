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
                <article class="news-list-item">
                    <div class="news-list-item__thumbnail">Article image coming soon</div>
                    <div class="news-list-item__content">
                        <p class="news-card__date">September 15, 2026</p>
                        <h3 class="news-card__title"><a href="news-article.php?id=1">Headline placeholder A</a></h3>
                        <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=1" class="text-link">Read more &rarr;</a>
                    </div>
                </article>
                <article class="news-list-item">
                    <div class="news-list-item__thumbnail">Article image coming soon</div>
                    <div class="news-list-item__content">
                        <p class="news-card__date">September 10, 2026</p>
                        <h3 class="news-card__title"><a href="news-article.php?id=2">Headline placeholder B</a></h3>
                        <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=2" class="text-link">Read more &rarr;</a>
                    </div>
                </article>
                <article class="news-list-item">
                    <div class="news-list-item__thumbnail">Article image coming soon</div>
                    <div class="news-list-item__content">
                        <p class="news-card__date">September 4, 2026</p>
                        <h3 class="news-card__title"><a href="news-article.php?id=3">Headline placeholder C</a></h3>
                        <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=3" class="text-link">Read more &rarr;</a>
                    </div>
                </article>
                <article class="news-list-item">
                    <div class="news-list-item__thumbnail">Article image coming soon</div>
                    <div class="news-list-item__content">
                        <p class="news-card__date">August 28, 2026</p>
                        <h3 class="news-card__title"><a href="news-article.php?id=4">Headline placeholder D</a></h3>
                        <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=4" class="text-link">Read more &rarr;</a>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
