<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/nav-footer.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/profile-booking.css">
</head>
<body class="site-body">
    <header class="site-header">
        <h1 class="site-title">Admin</h1>
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
                    <p class="metric-card__value">9</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="section-title">Recent Bookings</h2>
            <div class="booking-list">
                <div class="booking-card">
                    <div>
                        <p class="booking-card__title">Movie placeholder A</p>
                        <p class="booking-card__details">Guest: Quentin Tarantino &middot; 2 seats</p>
                    </div>
                    <span class="booking-card__status">Paid</span>
                </div>
                <div class="booking-card">
                    <div>
                        <p class="booking-card__title">Movie placeholder B</p>
                        <p class="booking-card__details">Placeholder Customer &middot; 1 seat</p>
                    </div>
                    <span class="booking-card__status">Pending</span>
                </div>
            </div>
            <a href="bookings.php" class="text-link">See all bookings &rarr;</a>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
