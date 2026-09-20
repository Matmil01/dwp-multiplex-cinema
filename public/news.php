<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../src/components/public-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-4">News</h2>

            <div class="space-y-4">
                <article class="bg-gray-900 rounded-lg p-4 flex gap-4">
                    <div class="w-32 h-20 shrink-0 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs">Thumbnail</div>
                    <div>
                        <p class="text-gray-500 text-xs mb-1">September 15, 2026</p>
                        <h3 class="font-semibold mb-1">Headline placeholder A</h3>
                        <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=1" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                    </div>
                </article>
                <article class="bg-gray-900 rounded-lg p-4 flex gap-4">
                    <div class="w-32 h-20 shrink-0 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs">Thumbnail</div>
                    <div>
                        <p class="text-gray-500 text-xs mb-1">September 10, 2026</p>
                        <h3 class="font-semibold mb-1">Headline placeholder B</h3>
                        <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=2" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                    </div>
                </article>
                <article class="bg-gray-900 rounded-lg p-4 flex gap-4">
                    <div class="w-32 h-20 shrink-0 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs">Thumbnail</div>
                    <div>
                        <p class="text-gray-500 text-xs mb-1">September 4, 2026</p>
                        <h3 class="font-semibold mb-1">Headline placeholder C</h3>
                        <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=3" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                    </div>
                </article>
                <article class="bg-gray-900 rounded-lg p-4 flex gap-4">
                    <div class="w-32 h-20 shrink-0 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs">Thumbnail</div>
                    <div>
                        <p class="text-gray-500 text-xs mb-1">August 28, 2026</p>
                        <h3 class="font-semibold mb-1">Headline placeholder D</h3>
                        <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                        <a href="news-article.php?id=4" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../src/components/footer.php'; ?>
</body>
</html>
