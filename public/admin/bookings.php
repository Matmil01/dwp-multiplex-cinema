<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Admin - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="../assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">Admin</h1>
        <?php require __DIR__ . '/../../src/components/admin-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-4">Bookings</h2>
            <table class="w-full text-left text-sm">
                <thead class="text-gray-400 border-b border-gray-800">
                    <tr>
                        <th class="pb-2">Customer</th>
                        <th class="pb-2">Movie</th>
                        <th class="pb-2">Screen</th>
                        <th class="pb-2">Date/Time</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <tr>
                        <td class="py-2">Quentin Tarantino</td>
                        <td class="py-2">Movie placeholder A</td>
                        <td class="py-2">Standard Digital</td>
                        <td class="py-2">Today, 7:00 PM</td>
                        <td class="py-2">Paid</td>
                        <td class="py-2"><a href="#" class="text-blue-400 hover:underline">Invoice</a></td>
                    </tr>
                    <tr>
                        <td class="py-2">Placeholder Customer</td>
                        <td class="py-2">Movie placeholder B</td>
                        <td class="py-2">IMAX</td>
                        <td class="py-2">Today, 9:30 PM</td>
                        <td class="py-2">Pending</td>
                        <td class="py-2"><a href="#" class="text-blue-400 hover:underline">Invoice</a></td>
                    </tr>
                    <tr>
                        <td class="py-2">Guest (jane@example.com)</td>
                        <td class="py-2">Movie placeholder C</td>
                        <td class="py-2">35mm</td>
                        <td class="py-2">Tomorrow, 8:00 PM</td>
                        <td class="py-2">Cancelled</td>
                        <td class="py-2"><a href="#" class="text-blue-400 hover:underline">Invoice</a></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <?php require __DIR__ . '/../../src/components/footer.php'; ?>
</body>
</html>
