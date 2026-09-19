<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screens - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../src/components/navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-4">Standard Digital</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="font-semibold">Standard Digital 1</p>
                    <p class="text-gray-400 text-sm">Movie placeholder</p>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="font-semibold">Standard Digital 2</p>
                    <p class="text-gray-400 text-sm">Movie placeholder</p>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="font-semibold">Standard Digital 3</p>
                    <p class="text-gray-400 text-sm">Movie placeholder</p>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="font-semibold">Standard Digital 4</p>
                    <p class="text-gray-400 text-sm">Movie placeholder</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">35mm</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="font-semibold">35mm Screen</p>
                    <p class="text-gray-400 text-sm">Movie placeholder</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">16mm</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="font-semibold">16mm Screen</p>
                    <p class="text-gray-400 text-sm">Movie placeholder</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">70mm</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="font-semibold">70mm Screen</p>
                    <p class="text-gray-400 text-sm">Movie placeholder</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">IMAX</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-blue-900 rounded-lg p-4">
                    <p class="font-semibold">IMAX Screen</p>
                    <p class="text-gray-400 text-sm">Movie placeholder</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">Drive-In</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="font-semibold">Drive-In Screen</p>
                    <p class="text-gray-400 text-sm">Movie placeholder</p>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../src/components/footer.php'; ?>
</body>
</html>
