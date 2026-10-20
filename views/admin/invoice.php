<?php
require __DIR__ . '/../../components/screen-format.php';

$total = (float) $booking['price_per_seat'] * count($booking['seats']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= (int) $booking['id'] ?> - Admin - Untitled Cinema</title>
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
            <h2 class="section-title">Invoice #<?= (int) $booking['id'] ?></h2>
            <p class="muted-text">Issued <?= htmlspecialchars(date('M j, Y', strtotime($booking['created_at'])), ENT_QUOTES) ?> &middot; <?= htmlspecialchars(ucfirst($booking['status']), ENT_QUOTES) ?></p>

            <p>
                <strong>Untitled Cinema</strong><br>
                <?= htmlspecialchars($company['address'] ?? '', ENT_QUOTES) ?><br>
                <?= htmlspecialchars($company['phone'] ?? '', ENT_QUOTES) ?>
            </p>

            <p>
                <strong>Billed to</strong><br>
                <?= htmlspecialchars($booking['customer_name'], ENT_QUOTES) ?><br>
                <?= htmlspecialchars($booking['customer_email'], ENT_QUOTES) ?>
            </p>

            <p>
                <strong><?= htmlspecialchars($booking['movie_title'], ENT_QUOTES) ?></strong><br>
                <?= htmlspecialchars(date('M j, Y, H:i', strtotime($booking['show_datetime'])), ENT_QUOTES) ?> &middot; <?= htmlspecialchars(screenDisplayName($booking['screen_type']), ENT_QUOTES) ?>
            </p>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Seat</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($booking['seats'] as $seat): ?>
                        <tr>
                            <td>Row <?= htmlspecialchars($seat['seat_row'], ENT_QUOTES) ?>, seat <?= (int) $seat['seat_number'] ?></td>
                            <td><?= number_format((float) $booking['price_per_seat'], 0) ?> kr.</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td><strong><?= number_format($total, 0) ?> kr.</strong></td>
                    </tr>
                </tbody>
            </table>

            <p><a href="/admin/bookings" class="inline-link">Back to bookings</a></p>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
