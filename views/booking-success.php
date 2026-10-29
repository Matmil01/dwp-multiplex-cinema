<?php
require __DIR__ . '/../components/screen-format.php';
require __DIR__ . '/../components/day-label.php';

$screenName = screenDisplayName($booking['screen_type']);
$whenLabel = showingDayLabel($booking['show_datetime']) . ', ' . date('H:i', strtotime($booking['show_datetime']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
    <link rel="stylesheet" href="/assets/css/seating.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title section-title--primary">You're Booked</h2>
            <p class="muted-text">A confirmation has been sent to <?= htmlspecialchars($booking['customer_email'], ENT_QUOTES) ?>.</p>

            <div class="booking-summary booking-summary--standalone">
                <h3 class="booking-summary__title"><?= htmlspecialchars($booking['movie_title'], ENT_QUOTES) ?></h3>
                <p class="booking-summary__showing"><?= htmlspecialchars($whenLabel, ENT_QUOTES) ?><br><?= htmlspecialchars($screenName, ENT_QUOTES) ?></p>
                <ul class="booking-summary__seats">
                    <?php foreach ($booking['seats'] as $seat): ?>
                        <li><span>Seat <?= htmlspecialchars($seat['seat_row'] . $seat['seat_number'], ENT_QUOTES) ?></span><span><?= number_format((float) $booking['price_per_seat'], 0) ?> kr.</span></li>
                    <?php endforeach; ?>
                </ul>
                <p class="booking-summary__total">
                    <span><?= count($booking['seats']) ?> <?= count($booking['seats']) === 1 ? 'seat' : 'seats' ?></span>
                    <span><?= number_format($total, 0) ?> kr.</span>
                </p>
                <a href="/profile" class="base-button booking-summary__continue">Go to your profile</a>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
