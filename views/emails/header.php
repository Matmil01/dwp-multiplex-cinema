<?php
// Opens every email: outer wrapper plus the cinema's name and contact line. Pair with footer.php.
// Styles are inline because mail clients ignore <link> and most <style> blocks.
$company = CompanyInfo::find();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Untitled Cinema</title>
</head>
<body style="margin: 0; padding: 24px; background: #f4f4f4; font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: #222;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; margin: 0 auto; background: #ffffff; border: 1px solid #ddd;">
        <tr>
            <td style="padding: 24px; border-bottom: 1px solid #ddd;">
                <h1 style="margin: 0 0 4px; font-size: 22px;">Untitled Cinema</h1>
                <p style="margin: 0; color: #666;"><?= htmlspecialchars($company['address'], ENT_QUOTES) ?> &middot; <?= htmlspecialchars($company['phone'], ENT_QUOTES) ?></p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
