<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - Admin - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="../assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">Admin</h1>
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">News Posts</h2>
                <a href="#" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Add Post</a>
            </div>
            <div class="space-y-3 max-w-lg">
                <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">Headline placeholder A</p>
                        <p class="text-gray-400 text-sm">September 15, 2026</p>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <a href="#" class="text-blue-400 hover:underline">Edit</a>
                        <a href="#" class="text-red-400 hover:underline">Delete</a>
                    </div>
                </div>
                <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">Headline placeholder B</p>
                        <p class="text-gray-400 text-sm">September 10, 2026</p>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <a href="#" class="text-blue-400 hover:underline">Edit</a>
                        <a href="#" class="text-red-400 hover:underline">Delete</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
