<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="../assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">Admin</h1>
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-4">Overview</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="text-gray-400 text-sm">Today's Bookings</p>
                    <p class="text-2xl font-bold">12</p>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="text-gray-400 text-sm">Pending Payments</p>
                    <p class="text-2xl font-bold">3</p>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <p class="text-gray-400 text-sm">Showings Today</p>
                    <p class="text-2xl font-bold">9</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">Recent Bookings</h2>
            <div class="space-y-3 max-w-lg">
                <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">Movie placeholder A</p>
                        <p class="text-gray-400 text-sm">Guest: Quentin Tarantino &middot; 2 seats</p>
                    </div>
                    <span class="text-gray-400 text-sm">Paid</span>
                </div>
                <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">Movie placeholder B</p>
                        <p class="text-gray-400 text-sm">Placeholder Customer &middot; 1 seat</p>
                    </div>
                    <span class="text-gray-400 text-sm">Pending</span>
                </div>
            </div>
            <a href="bookings.php" class="text-blue-400 text-sm hover:underline mt-3 inline-block">See all bookings &rarr;</a>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
