<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-1">Placeholder Customer</h2>
            <p class="text-gray-400 text-sm mb-4">placeholder@example.com &middot; 120 loyalty points</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">Booking History</h2>
            <div class="space-y-3 max-w-lg">
                <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">Movie placeholder A</p>
                        <p class="text-gray-400 text-sm">Sept 12, 2026 &middot; Standard Digital &middot; 2 seats</p>
                    </div>
                    <span class="text-gray-400 text-sm">Paid</span>
                </div>
                <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">Movie placeholder B</p>
                        <p class="text-gray-400 text-sm">Aug 30, 2026 &middot; IMAX &middot; 1 seat</p>
                    </div>
                    <span class="text-gray-400 text-sm">Cancelled</span>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
