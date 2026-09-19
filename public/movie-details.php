<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie placeholder A - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../src/components/navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section class="flex flex-col sm:flex-row gap-6">
            <div class="w-full sm:w-48 h-64 shrink-0 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-sm">Poster</div>
            <div>
                <h2 class="text-xl font-semibold mb-1">Movie placeholder A</h2>
                <p class="text-gray-500 text-sm mb-3">Drama, Mystery &middot; 118 min &middot; PG-13</p>
                <p class="text-gray-400">
                    Full movie description placeholder text goes here. This is where the
                    real synopsis will render once it's pulled from the database (or the
                    external movie API) instead of being hardcoded like this.
                </p>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">Showings</h2>
            <div class="space-y-3 max-w-lg">
                <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">Today, 7:00 PM</p>
                        <p class="text-gray-400 text-sm">Standard Digital 1</p>
                    </div>
                    <a href="#" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Book</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">Today, 9:30 PM</p>
                        <p class="text-gray-400 text-sm">IMAX</p>
                    </div>
                    <a href="#" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Book</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">Tomorrow, 8:00 PM</p>
                        <p class="text-gray-400 text-sm">35mm</p>
                    </div>
                    <a href="#" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Book</a>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../src/components/footer.php'; ?>
</body>
</html>
