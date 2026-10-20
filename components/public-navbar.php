<nav class="site-nav">
    <div class="site-nav__links">
        <a href="/movies">Movies</a>
        <a href="/screens">Screens</a>
        <a href="/news">News</a>
        <a href="/contact">Contact</a>
    </div>
    <p class="site-title"><a href="/" class="site-title__link">Untitled Cinema</a></p>
    <div class="site-nav__links site-nav__account-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="/admin">Admin</a>
            <?php endif; ?>
            <a href="/profile">Profile</a>
            <a href="/logout">Log out</a>
        <?php else: ?>
            <a href="/login">Login</a>
            <a href="/register">Register</a>
        <?php endif; ?>
    </div>
</nav>
