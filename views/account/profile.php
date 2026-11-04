<?php
require __DIR__ . '/../../components/screen-format.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
    <link rel="stylesheet" href="/assets/css/profile-booking.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <?php require __DIR__ . '/../../components/snackbar.php'; ?>
        <section>
            <h1 class="section-title section-title--primary">Your Profile</h1>
            <h2 class="profile-name"><?= htmlspecialchars($user['name'], ENT_QUOTES) ?></h2>
            <p class="profile-details">Email: <?= htmlspecialchars($user['email'], ENT_QUOTES) ?></p>
        </section>

        <section>
            <h2 class="section-title">Booking History</h2>
            <div class="booking-list">
                <?php if (empty($bookings)): ?>
                    <p class="muted-text">No bookings yet.</p>
                <?php endif; ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div>
                            <p class="booking-card__title"><?= htmlspecialchars($booking['movie_title'], ENT_QUOTES) ?></p>
                            <p class="booking-card__details"><?= htmlspecialchars(date('M j, Y, H:i', strtotime($booking['show_datetime'])), ENT_QUOTES) ?> &middot; <?= htmlspecialchars(formatBadgeLabel($booking['screen_type']), ENT_QUOTES) ?> &middot; <?= (int) $booking['seat_count'] ?> <?= (int) $booking['seat_count'] === 1 ? 'seat' : 'seats' ?></p>
                            <?php if (Booking::canCancel($booking)): ?>
                                <form method="post" action="/profile/cancel" data-confirm="Cancel this booking?" data-confirm-label="Cancel booking">
                                    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                    <button type="submit" class="danger-button">Cancel booking</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <span class="booking-card__status"><?= htmlspecialchars(ucfirst($booking['status']), ENT_QUOTES) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
    <script src="/assets/js/confirm-dialog.js"></script>
</body>
</html>
