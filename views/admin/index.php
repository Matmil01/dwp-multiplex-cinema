<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Untitled Cinema</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/nav-footer.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/profile-booking.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title">Overview</h2>
            <div class="metric-grid">
                <div class="metric-card">
                    <p class="metric-card__label">Today's Bookings</p>
                    <p class="metric-card__value">12</p>
                </div>
                <div class="metric-card">
                    <p class="metric-card__label">Pending Payments</p>
                    <p class="metric-card__value">3</p>
                </div>
                <div class="metric-card">
                    <p class="metric-card__label">Showings Today</p>
                    <p class="metric-card__value"><?= $showingsToday ?></p>
                </div>
            </div>
        </section>

    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
