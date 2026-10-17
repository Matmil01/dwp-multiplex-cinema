<?php
require __DIR__ . '/../../models/User.php';

session_start();

$email = trim($_POST['email'] ?? '');
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = User::findByEmail($email);

    // One generic message for both cases, so the form doesn't reveal which emails are registered.
    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = $user['role'];

        header('Location: ' . ($user['role'] === 'admin' ? '../admin/index.php' : 'profile.php'));
        exit;
    }

    $error = 'Incorrect email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Untitled Cinema</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/nav-footer.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title">Log In</h2>
            <form method="post" class="form-stack">
                <?php if ($error): ?>
                    <p class="form-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
                <?php endif; ?>
                <div class="form-field">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>" required>
                </div>
                <div class="form-field">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="base-button">Log In</button>
            </form>
            <p class="form-helper-text">Don't have an account? <a href="register.php" class="inline-link">Register</a></p>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
