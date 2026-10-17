<?php require __DIR__ . '/../../components/require-admin.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Admin - Untitled Cinema</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/nav-footer.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <h2 class="section-title">Bookings</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Movie</th>
                        <th>Screen</th>
                        <th>Date/Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Quentin Tarantino</td>
                        <td>Movie placeholder A</td>
                        <td>35mm</td>
                        <td>Today, 19:00</td>
                        <td>Paid</td>
                        <td><a href="#" class="inline-link">Invoice</a></td>
                    </tr>
                    <tr>
                        <td>Placeholder Customer</td>
                        <td>Movie placeholder B</td>
                        <td>IMAX</td>
                        <td>Today, 21:30</td>
                        <td>Pending</td>
                        <td><a href="#" class="inline-link">Invoice</a></td>
                    </tr>
                    <tr>
                        <td>Guest (jane@example.com)</td>
                        <td>Movie placeholder C</td>
                        <td>35mm</td>
                        <td>Tomorrow, 20:00</td>
                        <td>Cancelled</td>
                        <td><a href="#" class="inline-link">Invoice</a></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
