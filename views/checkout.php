<?php
require __DIR__ . '/../components/screen-format.php';
require __DIR__ . '/../components/day-label.php';

$screenName = screenDisplayName($showing['screen_type']);
$whenLabel = showingDayLabel($showing['show_datetime']) . ', ' . date('H:i', strtotime($showing['show_datetime']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Booking - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <link rel="stylesheet" href="/assets/css/seating.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title section-title--primary">Review Your Booking</h2>

            <form method="post" action="/checkout" class="booking-summary booking-summary--standalone">
                <h3 class="booking-summary__title"><?= htmlspecialchars($showing['movie_title'], ENT_QUOTES) ?></h3>
                <p class="booking-summary__showing"><?= htmlspecialchars($whenLabel, ENT_QUOTES) ?><br><?= htmlspecialchars($screenName, ENT_QUOTES) ?></p>
                <ul class="booking-summary__seats">
                    <?php foreach ($seats as $seat): ?>
                        <li><span>Seat <?= htmlspecialchars($seat, ENT_QUOTES) ?></span><span><?= number_format((float) $showing['ticket_price'], 0) ?> kr.</span></li>
                    <?php endforeach; ?>
                </ul>
                <p class="booking-summary__total">
                    <span><?= count($seats) ?> <?= count($seats) === 1 ? 'seat' : 'seats' ?></span>
                    <span><?= number_format($total, 0) ?> kr.</span>
                </p>
                <button type="submit" class="base-button booking-summary__continue">Confirm and pay</button>
                <?php if ($paymentEnabled): ?>
                    <p class="muted-text booking-summary__note">You will be sent to Stripe for secure payment.</p>
                <?php endif; ?>
                <p class="muted-text booking-summary__note"><a href="/book?showing=<?= (int) $showing['id'] ?>" class="inline-link">Change seats</a></p>
            </form>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
