<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="site-body">
    <header class="site-header">
        <h1 class="site-title">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title">Contact Us</h2>
            <form method="post" action="#" class="form-stack">
                <div class="form-field">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-input">
                </div>
                <div class="form-field">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input">
                </div>
                <div class="form-field">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-input">
                </div>
                <div class="form-field">
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" name="message" rows="5" class="form-input"></textarea>
                </div>
                <button type="submit" class="button">Send Message</button>
            </form>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
