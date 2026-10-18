<nav class="site-nav">
    <div class="site-nav__links">
        <a href="/movies.php">Movies</a>
        <a href="/screens.php">Screens</a>
        <a href="/news.php">News</a>
        <a href="/contact.php">Contact</a>
    </div>
    <p class="site-title"><a href="/index.php" class="site-title__link">Untitled Cinema</a></p>
    <div class="site-nav__links site-nav__account-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="/admin/index.php">Admin</a>
            <?php endif; ?>
            <a href="/account/profile.php">Profile</a>
            <a href="/account/logout.php">Log out</a>
        <?php else: ?>
            <a href="/account/login.php">Login</a>
            <a href="/account/register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>
