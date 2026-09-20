<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-4">Contact Us</h2>
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
                    <label for="subject" class="block text-sm text-gray-400 mb-1">Subject</label>
                    <input type="text" id="subject" name="subject" class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2">
                </div>
                <div>
                    <label for="message" class="block text-sm text-gray-400 mb-1">Message</label>
                    <textarea id="message" name="message" rows="5" class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2"></textarea>
                </div>
                <button type="submit" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Send Message</button>
            </form>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
