<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="../assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">Admin</h1>
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-4">Company Info</h2>
            <form method="post" action="#" class="max-w-lg space-y-4">
                <div>
                    <label for="description" class="block text-sm text-gray-400 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2"></textarea>
                </div>
                <div>
                    <label for="opening_hours" class="block text-sm text-gray-400 mb-1">Opening Hours</label>
                    <input type="text" id="opening_hours" name="opening_hours" class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2">
                </div>
                <div>
                    <label for="address" class="block text-sm text-gray-400 mb-1">Address</label>
                    <input type="text" id="address" name="address" class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2">
                </div>
                <div>
                    <label for="phone" class="block text-sm text-gray-400 mb-1">Phone</label>
                    <input type="text" id="phone" name="phone" class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2">
                </div>
                <button type="submit" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Save Changes</button>
            </form>
        </section>

        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">Staff Accounts</h2>
                <a href="#" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Invite Staff</a>
            </div>
            <div class="space-y-3 max-w-lg">
                <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-between">
                    <p class="font-semibold">Placeholder Staff Member</p>
                    <a href="#" class="text-red-400 text-sm hover:underline">Remove</a>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
