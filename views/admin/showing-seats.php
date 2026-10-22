<?php require __DIR__ . '/../../components/screen-format.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seats - Admin - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/seating.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="site-main">
        <?php require __DIR__ . '/../../components/snackbar.php'; ?>
        <section>
            <h2 class="section-title">Seats</h2>
            <p class="muted-text">
                <?= htmlspecialchars($showing['movie_title'], ENT_QUOTES) ?> &middot;
                <?= htmlspecialchars(date('M j, Y, H:i', strtotime($showing['show_datetime'])), ENT_QUOTES) ?> &middot;
                <?= htmlspecialchars(formatBadgeLabel($showing['screen_type']), ENT_QUOTES) ?>
            </p>

            <div class="seat-map seat-map--admin">
                <div class="seat-map__scroll">
                    <div class="seat-map__screen">Screen</div>

                    <?php for ($rowIndex = 0; $rowIndex < $showing['seat_rows']; $rowIndex++): ?>
                        <?php $row = chr(ord('A') + $rowIndex); ?>
                        <div class="seat-row">
                            <span class="seat-row__label"><?= $row ?></span>
                            <?php for ($number = 1; $number <= $showing['seats_per_row']; $number++): ?>
                                <?php $owner = $seatOwners[$row . $number] ?? null; ?>
                                <?php if ($owner): ?>
                                    <span class="seat seat--booked" role="img" aria-label="Seat <?= $row . $number ?>, booked by <?= htmlspecialchars($owner['customer_name'], ENT_QUOTES) ?>" data-tooltip="<?= htmlspecialchars($owner['customer_name'] . "\n" . $owner['customer_email'], ENT_QUOTES) ?>"><?= $number ?></span>
                                <?php else: ?>
                                    <span class="seat"><?= $number ?></span>
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
                    <li><span class="seat seat--legend seat--booked"></span> Booked</li>
                </ul>
            </div>

            <?php if (empty($seatOwners)): ?>
                <p class="empty-message">No seats booked for this showing.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Seats</th>
                            <th>Customer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars(implode(', ', $booking['seats']), ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($booking['customer_name'], ENT_QUOTES) ?> (<?= htmlspecialchars($booking['customer_email'], ENT_QUOTES) ?>)</td>
                                <td>
                                    <?php if (Booking::isOpen($showing)): ?>
                                        <form method="post" action="/admin/bookings/cancel" data-confirm="Cancel this booking?" data-confirm-label="Cancel booking">
                                            <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                            <input type="hidden" name="showing_id" value="<?= (int) $showing['id'] ?>">
                                            <button type="submit" class="danger-button">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
    <script src="/assets/js/confirm-dialog.js"></script>
</body>
</html>
