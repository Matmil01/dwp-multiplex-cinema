<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Untitled Cinema</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/nav-footer.css">
    <link rel="stylesheet" href="../assets/css/profile-booking.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="profile-name"><?= htmlspecialchars($user['name'], ENT_QUOTES) ?></h2>
            <p class="profile-details"><?= htmlspecialchars($user['email'], ENT_QUOTES) ?> &middot; <a href="logout.php" class="inline-link">Log out</a></p>
        </section>

        <section>
            <h2 class="section-title">Booking History</h2>
            <div class="booking-list">
                <div class="booking-card">
                    <div>
                        <p class="booking-card__title">Movie placeholder A</p>
                        <p class="booking-card__details">Sept 12, 2026 &middot; 35mm &middot; 2 seats</p>
                    </div>
                    <span class="booking-card__status">Paid</span>
                </div>
                <div class="booking-card">
                    <div>
                        <p class="booking-card__title">Movie placeholder B</p>
                        <p class="booking-card__details">Aug 30, 2026 &middot; IMAX &middot; 1 seat</p>
                    </div>
                    <span class="booking-card__status">Cancelled</span>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
