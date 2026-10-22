<?php
// Sent once after registration. Expects $name, $email and $siteUrl.
require __DIR__ . '/header.php';
?>
                <h2 style="margin: 0 0 16px; font-size: 18px;">Welcome, <?= htmlspecialchars($name, ENT_QUOTES) ?></h2>

                <p style="margin: 0 0 16px;">Your account is ready. Log in with <strong><?= htmlspecialchars($email, ENT_QUOTES) ?></strong> to book seats and keep track of your bookings.</p>

                <p style="margin: 0 0 24px;">
                    <a href="<?= htmlspecialchars($siteUrl, ENT_QUOTES) ?>/login" style="display: inline-block; padding: 10px 18px; background: #222; color: #ffffff; text-decoration: none;">Log in</a>
                </p>

                <p style="margin: 0; color: #666; font-size: 13px;">If you didn't create this account, you can ignore this email.</p>
<?php require __DIR__ . '/footer.php'; ?>
