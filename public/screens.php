<?php

require __DIR__ . '/../models/Showing.php';
require __DIR__ . '/../components/format-badge.php';

// Group the flat screen/showing rows into one entry per screen, dropping the placeholder
// showing row a LEFT JOIN produces when a screen has nothing scheduled.
$screensSchedule = [];
foreach (Showing::upcomingByScreen() as $row) {
    if (!isset($screensSchedule[$row['screen_id']])) {
        $screensSchedule[$row['screen_id']] = [
            'name' => $row['screen_name'],
            'type' => $row['screen_type'],
            'showings' => [],
        ];
    }
    if ($row['showing_id'] !== null) {
        $screensSchedule[$row['screen_id']]['showings'][] = $row;
    }
}

// "Today"/"Tomorrow" instead of the raw date, otherwise weekday + date (same idea as movie-details.php).
function screenShowingDayLabel(string $showDatetime): string
{
    $day = date('Y-m-d', strtotime($showDatetime));
    if ($day === date('Y-m-d')) {
        return 'Today';
    }
    if ($day === date('Y-m-d', strtotime('+1 day'))) {
        return 'Tomorrow';
    }
    return date('D, M j', strtotime($showDatetime));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screens - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../components/public-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <?php foreach ($screensSchedule as $screen): ?>
            <section class="bg-gray-900 rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-800">
                    <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border <?= htmlspecialchars(formatBadgeClasses($screen['type']), ENT_QUOTES) ?> mb-2"><?= htmlspecialchars($screen['type'], ENT_QUOTES) ?></span>
                    <h2 class="text-xl font-semibold mb-1"><?= htmlspecialchars($screen['name'], ENT_QUOTES) ?></h2>
                    <p class="text-gray-400 text-sm"><?= htmlspecialchars(formatBlurb($screen['type']), ENT_QUOTES) ?></p>
                </div>
                <table class="w-full text-sm table-fixed">
                    <thead>
                        <tr class="text-gray-500 text-xs uppercase tracking-widest text-left">
                            <th class="px-6 py-3 font-normal w-1/2">Movie</th>
                            <th class="px-6 py-3 font-normal w-1/4">Date</th>
                            <th class="px-6 py-3 font-normal w-1/4">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <?php if (empty($screen['showings'])): ?>
                            <tr>
                                <td class="px-6 py-4 text-gray-500 text-center" colspan="3">Nothing currently scheduled.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($screen['showings'] as $showing): ?>
                            <tr>
                                <td class="px-6 py-3"><a href="movie-details.php?id=<?= (int) $showing['movie_id'] ?>" class="hover:underline"><?= htmlspecialchars($showing['movie_title'], ENT_QUOTES) ?></a></td>
                                <td class="px-6 py-3 text-gray-400"><?= htmlspecialchars(screenShowingDayLabel($showing['show_datetime']), ENT_QUOTES) ?></td>
                                <td class="px-6 py-3"><a href="#" class="text-xs bg-gray-800 hover:bg-blue-900 rounded px-2 py-1"><?= htmlspecialchars(date('g:i A', strtotime($showing['show_datetime'])), ENT_QUOTES) ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endforeach; ?>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
