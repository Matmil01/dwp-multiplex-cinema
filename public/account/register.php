<?php
session_start();
require __DIR__ . '/../../models/User.php';

const PASSWORD_MIN_LENGTH = 8;

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if ($name === '' || strlen($name) > 100) {
        $error = 'Please enter your name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    } elseif ($password !== ($_POST['password_confirm'] ?? '')) {
        $error = 'Passwords do not match.';
    } elseif (User::emailExists($email)) {
        $error = 'An account with that email already exists.';
    } else {
        User::create($name, $email, $password);
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Untitled Cinema</title>
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
            <h2 class="section-title">Register</h2>
            <form method="post" class="form-stack">
                <?php if ($error): ?>
                    <p class="form-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
                <?php endif; ?>
                <div class="form-field">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-input" value="<?= htmlspecialchars($name, ENT_QUOTES) ?>" required>
                </div>
                <div class="form-field">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>" required>
                </div>
                <div class="form-field">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                <div class="form-field">
                    <label for="password_confirm" class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-input" required>
                </div>
                <button type="submit" class="base-button">Register</button>
            </form>
            <p class="form-helper-text">Already have an account? <a href="login.php" class="inline-link">Log in</a></p>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
