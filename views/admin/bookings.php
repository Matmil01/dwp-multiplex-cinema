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
        <section>
            <h2 class="section-title">Bookings</h2>
            <?php if (empty($bookings)): ?>
                <p class="empty-message">No bookings yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Movie</th>
                            <th>Screen</th>
                            <th>Date/Time</th>
                            <th>Seats</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['customer_name'], ENT_QUOTES) ?> (<?= htmlspecialchars($booking['customer_email'], ENT_QUOTES) ?>)</td>
                                <td><?= htmlspecialchars($booking['movie_title'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars(formatBadgeLabel($booking['screen_type']), ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars(date('M j, Y, H:i', strtotime($booking['show_datetime'])), ENT_QUOTES) ?></td>
                                <td><?= (int) $booking['seat_count'] ?></td>
                                <td><?= number_format((float) $booking['price_per_seat'] * (int) $booking['seat_count'], 0) ?> kr.</td>
                                <td><?= htmlspecialchars(ucfirst($booking['status']), ENT_QUOTES) ?></td>
                                <td>
                                    <?php if ($booking['status'] !== 'cancelled'): ?>
                                        <a href="/admin/bookings/invoice?id=<?= (int) $booking['id'] ?>" class="inline-link">Invoice</a>
                                        <form method="post" action="/admin/bookings/cancel" onsubmit="return confirm('Cancel this booking?')">
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
</body>
</html>
