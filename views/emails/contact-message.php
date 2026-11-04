<?php
require __DIR__ . '/header.php';
?>

                <h2 style="margin: 0 0 16px; font-size: 18px;">New message from the contact form</h2>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 16px; color: #666;">
                    <tr><td>From</td><td align="right"><?= htmlspecialchars($name, ENT_QUOTES) ?> (<?= htmlspecialchars($email, ENT_QUOTES) ?>)</td></tr>
                    <tr><td>Subject</td><td align="right"><?= htmlspecialchars($subject, ENT_QUOTES) ?></td></tr>
                    <tr><td>Sent</td><td align="right"><?= date('j M Y, H:i') ?></td></tr>
                </table>

                <p style="margin: 0; padding: 16px; background: #f4f4f4; white-space: pre-wrap;"><?= htmlspecialchars($message, ENT_QUOTES) ?></p>

                <p style="margin: 24px 0 0; color: #666; font-size: 13px;">Replying to this email goes to the sender.</p>
<?php require __DIR__ . '/footer.php'; ?>
