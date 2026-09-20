<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Headline placeholder - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../src/components/public-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <article>
            <div class="w-full h-48 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-sm mb-4">Thumbnail</div>
            <p class="text-gray-500 text-xs mb-1">September 15, 2026</p>
            <h2 class="text-xl font-semibold mb-4">Headline placeholder A</h2>
            <p class="text-gray-400">
                Full post body placeholder text goes here. This is where the entire news
                post content will render once it's pulled from the database instead of
                being hardcoded like this.
            </p>
        </article>
    </main>

    <?php require __DIR__ . '/../src/components/footer.php'; ?>
</body>
</html>
