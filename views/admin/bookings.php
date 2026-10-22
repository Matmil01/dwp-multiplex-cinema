<?php require __DIR__ . '/../../components/screen-format.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Admin - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="site-main">
        <?php require __DIR__ . '/../../components/snackbar.php'; ?>
        <section>
            <h2 class="section-title">Bookings</h2>
            <?php if (empty($bookings)): ?>
                <p class="empty-message">No bookings yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Customer</th>
                            <th>Movie</th>
                            <th>Screen</th>
                            <th>Seats</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><a href="/admin/showing-seats?showing=<?= (int) $booking['showing_id'] ?>" class="inline-link"><?= htmlspecialchars(date('M j, Y, H:i', strtotime($booking['show_datetime'])), ENT_QUOTES) ?></a></td>
                                <td><?= htmlspecialchars($booking['customer_name'], ENT_QUOTES) ?> (<?= htmlspecialchars($booking['customer_email'], ENT_QUOTES) ?>)</td>
                                <td><?= htmlspecialchars($booking['movie_title'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars(formatBadgeLabel($booking['screen_type']), ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($booking['seat_names'] ?? '-', ENT_QUOTES) ?></td>
                                <td><?= number_format((float) $booking['price_per_seat'] * (int) $booking['seat_count'], 0) ?> kr.</td>
                                <td><?= htmlspecialchars(ucfirst($booking['status']), ENT_QUOTES) ?></td>
                                <td>
                                    <?php if (Booking::canCancel($booking)): ?>
                                        <form method="post" action="/admin/bookings/cancel" data-confirm="Cancel this booking?" data-confirm-label="Cancel booking">
                                            <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
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
