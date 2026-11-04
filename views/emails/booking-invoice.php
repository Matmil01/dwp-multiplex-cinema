<?php
require __DIR__ . '/../../components/screen-format.php';

$total = (float) $booking['price_per_seat'] * count($booking['seats']);
$vat = $total - $total / 1.25; // Danish prices include 25% VAT; this is the VAT share of the total.

require __DIR__ . '/header.php';
?>
                <p style="margin: 0 0 16px;">Hi <?= htmlspecialchars($booking['customer_name'], ENT_QUOTES) ?>, your booking is confirmed. Here is your receipt.</p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 16px; color: #666;">
                    <tr><td>Invoice no.</td><td align="right">#<?= (int) $booking['id'] ?></td></tr>
                    <tr><td>Issued</td><td align="right"><?= date('j M Y, H:i', strtotime($booking['created_at'])) ?></td></tr>
                    <tr><td>Billed to</td><td align="right"><?= htmlspecialchars($booking['customer_name'], ENT_QUOTES) ?> (<?= htmlspecialchars($booking['customer_email'], ENT_QUOTES) ?>)</td></tr>
                </table>

                <h2 style="margin: 0 0 4px; font-size: 18px;"><?= htmlspecialchars($booking['movie_title'], ENT_QUOTES) ?></h2>
                <p style="margin: 0 0 16px; color: #666;"><?= date('l j F Y, H:i', strtotime($booking['show_datetime'])) ?> &middot; <?= htmlspecialchars(screenDisplayName($booking['screen_type']), ENT_QUOTES) ?></p>

                <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;">
                    <?php foreach ($booking['seats'] as $seat): ?>
                        <tr>
                            <td>Seat <?= htmlspecialchars($seat['seat_row'] . $seat['seat_number'], ENT_QUOTES) ?></td>
                            <td align="right"><?= number_format((float) $booking['price_per_seat'], 2) ?> kr.</td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <table role="presentation" width="100%" cellpadding="6" cellspacing="0">
                    <tr style="font-size: 18px; font-weight: bold;">
                        <td>Total</td>
                        <td align="right"><?= number_format($total, 2) ?> kr.</td>
                    </tr>
                    <tr style="color: #666;">
                        <td>Of which 25% VAT</td>
                        <td align="right"><?= number_format($vat, 2) ?> kr.</td>
                    </tr>
                </table>

                <p style="margin: 24px 0 0; color: #666; font-size: 13px;">Show this email or your booking number at the entrance. You can cancel from your profile until the showing starts.</p>
<?php require __DIR__ . '/footer.php'; ?>
