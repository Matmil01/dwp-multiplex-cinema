<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <?php require __DIR__ . '/../components/snackbar.php'; ?>
            <h2 class="section-title section-title--primary">Contact Us</h2>
            <form method="post" action="/contact" class="form-stack">
                <?php if ($error): ?>
                    <p class="form-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
                <?php endif; ?>
                <div class="form-field">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" maxlength="100" class="form-input" value="<?= htmlspecialchars($name, ENT_QUOTES) ?>" required>
                </div>
                <div class="form-field">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" maxlength="150" class="form-input" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>" required>
                </div>
                <div class="form-field">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" id="subject" name="subject" maxlength="150" class="form-input" value="<?= htmlspecialchars($subject, ENT_QUOTES) ?>" required>
                </div>
                <div class="form-field">
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" name="message" rows="5" maxlength="5000" class="form-input" required><?= htmlspecialchars($message, ENT_QUOTES) ?></textarea>
                </div>
                <button type="submit" class="base-button">Send Message</button>
            </form>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
