<?php

require __DIR__ . '/../../models/Showing.php';
require __DIR__ . '/../../models/Movie.php';

$date = $_GET['date'] ?? date('Y-m-d');
$saveError = null;
$editor = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    if ((int) $_POST['movie_id'] === 0) {
        $saveError = 'Pick a movie from the search results before saving.';
    } else {
        try {
            Showing::save([
                'id' => $_POST['id'] !== '' ? (int) $_POST['id'] : null,
                'movie_id' => (int) $_POST['movie_id'],
                'screen_id' => (int) $_POST['screen_id'],
                'show_datetime' => $date . ' ' . $_POST['show_time'] . ':00',
            ]);
            header('Location: schedule.php?date=' . urlencode($date) . '&saved=1');
            exit;
        } catch (Throwable $e) {
            $saveError = $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    Showing::delete((int) $_POST['id']);
    header('Location: schedule.php?date=' . urlencode($date) . '&deleted=1');
    exit;
}

if (isset($_GET['edit_id'])) {
    $editor = Showing::find((int) $_GET['edit_id']);
    if ($editor) {
        $editor['show_time'] = date('H:i', strtotime($editor['show_datetime']));
    }
}

$movies = Movie::all();
$screens = Showing::screens();

$showingsByScreen = [];
foreach (Showing::forDate($date) as $showing) {
    $showingsByScreen[$showing['screen_id']][] = $showing;
}

$weekStart = strtotime('monday this week', strtotime($date));
$weekDays = [];
for ($i = 0; $i < 7; $i++) {
    $weekDays[] = date('Y-m-d', strtotime("+$i days", $weekStart));
}
$prevWeek = date('Y-m-d', strtotime('-7 days', strtotime($date)));
$nextWeek = date('Y-m-d', strtotime('+7 days', strtotime($date)));

// Grid covers 12 PM-11:59 PM (12 columns); returns a block's left/width as % of that 12-hour span.
function timelineStyle(string $showDatetime, ?int $durationMinutes): array
{
    $startOfSpan = strtotime(date('Y-m-d', strtotime($showDatetime)) . ' 12:00');
    $minutesIn = (strtotime($showDatetime) - $startOfSpan) / 60;
    $span = 12 * 60;

    $left = max(0, min(100, $minutesIn / $span * 100));
    $width = max(3, min(100 - $left, ($durationMinutes ?: 90) / $span * 100));

    return [$left, $width];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - Admin - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="../assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">Admin</h1>
        <?php require __DIR__ . '/../../src/components/admin-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold">Schedule</h2>
            </div>

            <div class="overflow-x-auto">
                <div class="min-w-190">
                    <div class="grid grid-cols-[110px_repeat(12,1fr)] text-xs text-gray-500 mb-2">
                        <div></div>
                        <?php for ($hour = 12; $hour <= 23; $hour++): ?>
                            <div><?= $hour ?>:00</div>
                        <?php endfor; ?>
                    </div>

                    <div class="space-y-2">
                        <?php foreach ($screens as $screen): ?>
                            <div class="grid grid-cols-[110px_repeat(12,1fr)] items-center">
                                <div class="text-sm text-gray-400 pr-2"><?= htmlspecialchars($screen['name'], ENT_QUOTES) ?></div>
                                <div class="col-span-12 h-14 relative rounded-lg overflow-hidden border border-gray-800 grid grid-cols-12">
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="bg-gray-900"></div>
                                    <?php foreach ($showingsByScreen[$screen['id']] ?? [] as $showing): ?>
                                        <?php [$left, $width] = timelineStyle($showing['show_datetime'], $showing['duration_minutes']); ?>
                                        <a href="?date=<?= htmlspecialchars($date, ENT_QUOTES) ?>&edit_id=<?= (int) $showing['id'] ?>" class="absolute inset-y-1 rounded-md border border-amber-400 bg-amber-950/70 px-2 flex items-center text-xs text-amber-200 whitespace-nowrap overflow-hidden hover:bg-amber-900/70" style="left: <?= $left ?>%; width: <?= $width ?>%;">
                                            <?= htmlspecialchars(date('H:i', strtotime($showing['show_datetime'])), ENT_QUOTES) ?> &middot; <?= htmlspecialchars($showing['movie_title'], ENT_QUOTES) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-3"><?= $editor ? 'Edit Showing' : 'Add Showing' ?></h3>

            <?php if (isset($_GET['saved'])): ?>
                <p class="text-green-400 text-sm mb-4">Showing saved.</p>
            <?php elseif (isset($_GET['deleted'])): ?>
                <p class="text-green-400 text-sm mb-4">Showing deleted.</p>
            <?php endif; ?>
            <?php if ($saveError): ?>
                <p class="text-red-400 text-sm mb-4"><?= htmlspecialchars($saveError, ENT_QUOTES) ?></p>
            <?php endif; ?>

            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4 space-y-4">
                <div class="flex items-center gap-2">
                    <a href="?date=<?= htmlspecialchars($prevWeek, ENT_QUOTES) ?>" class="text-gray-400 hover:text-white px-1">&lsaquo;</a>
                    <div class="flex gap-1 bg-gray-800 rounded-lg p-1">
                        <?php foreach ($weekDays as $day): ?>
                            <a href="?date=<?= htmlspecialchars($day, ENT_QUOTES) ?>" class="px-3 py-1.5 rounded-md text-sm <?= $day === $date ? 'bg-blue-900 text-white' : 'text-gray-400 hover:text-white' ?>"><?= htmlspecialchars(date('D j', strtotime($day)), ENT_QUOTES) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <a href="?date=<?= htmlspecialchars($nextWeek, ENT_QUOTES) ?>" class="text-gray-400 hover:text-white px-1">&rsaquo;</a>
                </div>

                <form method="post" class="flex flex-wrap items-end gap-3">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($editor['id'] ?? ''), ENT_QUOTES) ?>">
                    <input type="hidden" name="movie_id" id="movieId" value="<?= htmlspecialchars((string) ($editor['movie_id'] ?? ''), ENT_QUOTES) ?>">

                    <div class="relative">
                        <label class="text-gray-400 text-sm block mb-1">Movie</label>
                        <input type="text" id="movieSearch" autocomplete="off" placeholder="Search by title&hellip;" value="<?= htmlspecialchars($editor['movie_title'] ?? '', ENT_QUOTES) ?>" class="bg-gray-800 rounded-lg px-3 py-2 text-sm w-56">
                        <div id="movieResults" class="hidden absolute z-10 top-full mt-1 w-56 bg-gray-800 border border-gray-700 rounded-lg overflow-hidden"></div>
                    </div>

                    <div>
                        <label class="text-gray-400 text-sm block mb-1">Screen</label>
                        <select name="screen_id" required class="bg-gray-800 rounded-lg px-3 py-2 text-sm">
                            <?php foreach ($screens as $screen): ?>
                                <option value="<?= (int) $screen['id'] ?>" <?= (($editor['screen_id'] ?? null) == $screen['id']) ? 'selected' : '' ?>><?= htmlspecialchars($screen['name'], ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="text-gray-400 text-sm block mb-1">Time</label>
                        <input type="time" name="show_time" value="<?= htmlspecialchars($editor['show_time'] ?? '18:00', ENT_QUOTES) ?>" required class="bg-gray-800 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <button type="submit" class="bg-blue-900 rounded-lg px-4 py-2 text-sm">Save</button>
                    <?php if ($editor): ?>
                        <a href="?date=<?= htmlspecialchars($date, ENT_QUOTES) ?>" class="text-gray-400 hover:text-white text-sm px-2">Cancel</a>
                    <?php endif; ?>
                </form>

                <?php if ($editor): ?>
                    <form method="post" onsubmit="return confirm('Delete this showing?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $editor['id'] ?>">
                        <button type="submit" class="text-red-400 hover:underline text-sm">Delete this showing</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        // Type-to-search movie picker: filters the catalog client-side (same trick as the
        // catalog filter on the Movies admin page) instead of a <select> with hundreds of options.
        const movies = <?= json_encode(array_map(fn($m) => ['id' => (int) $m['id'], 'title' => $m['title']], $movies)) ?>;
        const searchInput = document.getElementById('movieSearch');
        const resultsBox = document.getElementById('movieResults');
        const movieIdInput = document.getElementById('movieId');

        searchInput.addEventListener('input', function () {
            movieIdInput.value = '';
            const term = this.value.trim().toLowerCase();

            if (term === '') {
                resultsBox.classList.add('hidden');
                resultsBox.innerHTML = '';
                return;
            }

            const matches = movies.filter(m => m.title.toLowerCase().includes(term)).slice(0, 8);

            resultsBox.innerHTML = matches.map(m =>
                `<button type="button" data-id="${m.id}" data-title="${m.title.replace(/"/g, '&quot;')}" class="block w-full text-left px-3 py-2 text-sm hover:bg-gray-700">${m.title}</button>`
            ).join('') || '<p class="px-3 py-2 text-sm text-gray-500">No matches</p>';

            resultsBox.classList.remove('hidden');
        });

        resultsBox.addEventListener('click', function (event) {
            const button = event.target.closest('button[data-id]');
            if (!button) {
                return;
            }

            movieIdInput.value = button.dataset.id;
            searchInput.value = button.dataset.title;
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
        });
    </script>

    <?php require __DIR__ . '/../../src/components/footer.php'; ?>
</body>
</html>
