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
    <title>Choose Seats - Untitled Cinema</title>
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
            <h2 class="section-title section-title--primary">Choose Your Seats</h2>
            <p class="muted-text"><?= htmlspecialchars($showing['movie_title'], ENT_QUOTES) ?> &middot; <?= htmlspecialchars($whenLabel, ENT_QUOTES) ?> &middot; <?= htmlspecialchars($screenName, ENT_QUOTES) ?></p>

            <form method="post" action="/book" data-price="<?= (float) $showing['ticket_price'] ?>">
            <input type="hidden" name="showing_id" value="<?= (int) $showing['id'] ?>">
            <div class="seating">
                <div class="seat-map">
                    <div class="seat-map__scroll">
                        <div class="seat-map__screen">Screen</div>

                        <?php for ($rowIndex = 0; $rowIndex < $showing['seat_rows']; $rowIndex++): ?>
                            <?php $row = chr(ord('A') + $rowIndex); ?>
                            <div class="seat-row">
                                <span class="seat-row__label"><?= $row ?></span>
                                <?php for ($number = 1; $number <= $showing['seats_per_row']; $number++): ?>
                                    <?php $seatId = $row . $number; ?>
                                    <?php if (in_array($seatId, $takenSeats, true)): ?>
                                        <span class="seat seat--taken" role="img" aria-label="Row <?= $row ?>, seat <?= $number ?>, taken"><?= $number ?></span>
                                    <?php else: ?>
                                        <label class="seat-pick">
                                            <input type="checkbox" name="seats[]" value="<?= $seatId ?>"
                                                   aria-label="Row <?= $row ?>, seat <?= $number ?>"<?= in_array($seatId, $selectedSeats, true) ? ' checked' : '' ?>>
                                            <span class="seat"><?= $number ?></span>
                                        </label>
                                    <?php endif; ?>
                                    <?php if ($number === (int) $showing['aisle_after']): ?>
                                        <span class="seat-row__aisle"></span>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span class="seat-row__label"><?= $row ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <ul class="seat-legend">
                        <li><span class="seat seat--legend"></span> Available</li>
                        <li><span class="seat seat--legend seat--selected"></span> Selected</li>
                        <li><span class="seat seat--legend seat--taken"></span> Taken</li>
                    </ul>
                </div>

                <aside class="booking-summary">
                    <h3 class="booking-summary__title">Your Booking</h3>
                    <p class="booking-summary__showing"><?= htmlspecialchars($showing['movie_title'], ENT_QUOTES) ?><br><?= htmlspecialchars($whenLabel, ENT_QUOTES) ?><br><?= htmlspecialchars($screenName, ENT_QUOTES) ?></p>
                    <p class="booking-summary__total">
                        <span id="booking-count">0 seats</span>
                        <span id="booking-total">0 kr.</span>
                    </p>
                    <?php if ($error): ?>
                        <p class="form-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
                    <?php endif; ?>
                    <button type="submit" class="base-button booking-summary__continue">Book seats</button>
                    <p class="muted-text booking-summary__note"><?= number_format((float) $showing['ticket_price'], 0) ?> kr. per seat. Up to <?= Booking::MAX_SEATS ?> seats per booking.</p>
                </aside>
            </div>
            </form>
        </section>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
    <script src="/assets/js/booking-total.js"></script>
</body>
</html>
