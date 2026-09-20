<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-4">Register</h2>
            <form method="post" action="#" class="max-w-lg space-y-4">
                <div>
                    <label for="name" class="block text-sm text-gray-400 mb-1">Name</label>
                    <input type="text" id="name" name="name" class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2">
                </div>
                <div>
                    <label for="email" class="block text-sm text-gray-400 mb-1">Email</label>
                    <input type="email" id="email" name="email" class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2">
                </div>
                <div>
                    <label for="password" class="block text-sm text-gray-400 mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2">
                </div>
                <div>
                    <label for="password_confirm" class="block text-sm text-gray-400 mb-1">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2">
                </div>
                <button type="submit" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Register</button>
            </form>
            <p class="text-gray-400 text-sm mt-4">Already have an account? <a href="login.php" class="text-blue-400 hover:underline">Log in</a></p>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
