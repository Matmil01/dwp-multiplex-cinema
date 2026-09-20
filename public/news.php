<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/main.css">
</head>
<body class="site-body">
    <header class="site-header">
        <h1 class="site-title">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title">News</h2>

            <div class="news-list">
                <article class="news-list-item">
                    <div class="news-list-item__thumbnail">Thumbnail</div>
                    <div>
                        <p class="news-card__date">September 15, 2026</p>
                        <h3 class="news-card__title">Headline placeholder A</h3>
                        <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=1" class="text-link">Read more &rarr;</a>
                    </div>
                </article>
                <article class="news-list-item">
                    <div class="news-list-item__thumbnail">Thumbnail</div>
                    <div>
                        <p class="news-card__date">September 10, 2026</p>
                        <h3 class="news-card__title">Headline placeholder B</h3>
                        <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=2" class="text-link">Read more &rarr;</a>
                    </div>
                </article>
                <article class="news-list-item">
                    <div class="news-list-item__thumbnail">Thumbnail</div>
                    <div>
                        <p class="news-card__date">September 4, 2026</p>
                        <h3 class="news-card__title">Headline placeholder C</h3>
                        <p class="news-card__excerpt">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=3" class="text-link">Read more &rarr;</a>
                    </div>
                </article>
                <article class="news-list-item">
                    <div class="news-list-item__thumbnail">Thumbnail</div>
                    <div>
                        <p class="news-card__date">August 28, 2026</p>
                        <h3 class="news-card__title">Headline placeholder D</h3>
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
