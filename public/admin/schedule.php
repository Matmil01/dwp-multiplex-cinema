<?php

require __DIR__ . '/../../models/Showing.php';
require __DIR__ . '/../../models/Movie.php';
require __DIR__ . '/../../models/CompanyInfo.php';
require __DIR__ . '/../../demo/AutoSchedule.php';
require __DIR__ . '/../../components/screen-format.php';

$config = require __DIR__ . '/../../config/config.php';
if ($config['auto_schedule_enabled']) {
    AutoSchedule::ensureBuffer();
}

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

// Drag-and-drop grid endpoint: same save path as the form, just responds with JSON instead of redirecting.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'move') {
    header('Content-Type: application/json');
    try {
        Showing::save([
            'id' => (int) $_POST['id'],
            'movie_id' => (int) $_POST['movie_id'],
            'screen_id' => (int) $_POST['screen_id'],
            'show_datetime' => $_POST['show_datetime'],
        ]);
        echo json_encode(['ok' => true]);
    } catch (Throwable $e) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Duplicate-on-timeline endpoint: same movie/screen as the clicked block, placed right after it ends.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'duplicate') {
    header('Content-Type: application/json');
    try {
        $newId = Showing::save([
            'id' => null,
            'movie_id' => (int) $_POST['movie_id'],
            'screen_id' => (int) $_POST['screen_id'],
            'show_datetime' => $_POST['show_datetime'],
        ]);
        echo json_encode(['ok' => true, 'id' => $newId]);
    } catch (Throwable $e) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if (isset($_GET['edit_id'])) {
    $editor = Showing::find((int) $_GET['edit_id']);
    if ($editor) {
        $editor['show_time'] = date('H:i', strtotime($editor['show_datetime']));
    }
}

$company = CompanyInfo::find();
$openingHour = (int) substr($company['opening_time'], 0, 2);
// Rounds a closing time with leftover minutes (e.g. 23:59) up to the next full hour column.
$closingHour = (int) substr($company['closing_time'], 0, 2) + ((int) substr($company['closing_time'], 3, 2) > 0 ? 1 : 0);

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

// Grid covers the company's opening-closing window; returns a block's left/width as % of that span.
function timelineStyle(string $showDatetime, ?int $durationMinutes, int $openingHour, int $closingHour): array
{
    $startOfSpan = strtotime(date('Y-m-d', strtotime($showDatetime)) . " $openingHour:00");
    $minutesIn = (strtotime($showDatetime) - $startOfSpan) / 60;
    $span = ($closingHour - $openingHour) * 60;

    $left = max(0, min(100, $minutesIn / $span * 100));
    $width = max(3, min(100 - $left, ($durationMinutes ?: 90) / $span * 100));

    return [$left, $width];
}

// Raw (unclamped) minutes since the grid's opening-hour start, for the drag script's snapping math.
function timelineMinutes(string $showDatetime, int $openingHour): int
{
    $startOfSpan = strtotime(date('Y-m-d', strtotime($showDatetime)) . " $openingHour:00");

    return (int) round((strtotime($showDatetime) - $startOfSpan) / 60);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - Admin - Untitled Cinema</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/nav-footer.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <div class="page-heading">
                <h2 class="section-title">Schedule</h2>
            </div>

            <div class="schedule-board-wrap">
                <div class="schedule-board">
                    <?php $hourCount = $closingHour - $openingHour; ?>
                    <div class="schedule-board__hours">
                        <div></div>
                        <?php for ($hour = $openingHour; $hour < $closingHour; $hour++): ?>
                            <div><?= $hour ?>:00</div>
                        <?php endfor; ?>
                    </div>

                    <div class="schedule-board__rows">
                        <?php foreach ($screens as $screen): ?>
                            <div class="schedule-board__row">
                                <div class="schedule-board__screen-name"><?= htmlspecialchars(screenDisplayName($screen['type']), ENT_QUOTES) ?></div>
                                <div class="schedule-board__timeline" data-screen-id="<?= (int) $screen['id'] ?>">
                                    <?php for ($i = 0; $i < $hourCount; $i++): ?>
                                        <div class="schedule-board__hour<?= $i === $hourCount - 1 ? ' schedule-board__hour--last' : '' ?>"></div>
                                    <?php endfor; ?>
                                    <?php foreach ($showingsByScreen[$screen['id']] ?? [] as $showing): ?>
                                        <?php
                                        [$left, $width] = timelineStyle($showing['show_datetime'], $showing['duration_minutes'], $openingHour, $closingHour);
                                        $startTime = date('H:i', strtotime($showing['show_datetime']));
                                        $endTime = date('H:i', strtotime($showing['show_datetime']) + ($showing['duration_minutes'] ?: 90) * 60);
                                        ?>
                                        <div class="schedule-board__showing"
                                           style="left: <?= $left ?>%; width: <?= $width ?>%;"
                                           title="<?= htmlspecialchars("$startTime\u{2013}$endTime \u{00b7} {$showing['movie_title']}", ENT_QUOTES) ?>"
                                           data-id="<?= (int) $showing['id'] ?>"
                                           data-movie-id="<?= (int) $showing['movie_id'] ?>"
                                           data-minutes="<?= timelineMinutes($showing['show_datetime'], $openingHour) ?>"
                                           data-duration="<?= (int) ($showing['duration_minutes'] ?: 90) ?>"
                                           data-edit-href="?date=<?= htmlspecialchars($date, ENT_QUOTES) ?>&edit_id=<?= (int) $showing['id'] ?>">
                                            <span class="schedule-board__showing-label">
                                                <span class="schedule-board__showing-time"><?= htmlspecialchars($startTime, ENT_QUOTES) ?>&ndash;<?= htmlspecialchars($endTime, ENT_QUOTES) ?></span>
                                                <span class="schedule-board__showing-title"><?= htmlspecialchars($showing['movie_title'], ENT_QUOTES) ?></span>
                                            </span>
                                            <button type="button" class="schedule-board__duplicate" title="Duplicate this showing right after it">+</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <h3 class="admin-subheading"><?= $editor ? 'Edit Showing' : 'Add Showing' ?></h3>

            <?php if (isset($_GET['saved'])): ?>
                <p class="notice notice--success">Showing saved.</p>
            <?php elseif (isset($_GET['deleted'])): ?>
                <p class="notice notice--success">Showing deleted.</p>
            <?php endif; ?>
            <?php if ($saveError): ?>
                <p class="notice notice--error"><?= htmlspecialchars($saveError, ENT_QUOTES) ?></p>
            <?php endif; ?>

            <div class="schedule-editor">
                <div class="week-navigation">
                    <a href="?date=<?= htmlspecialchars($prevWeek, ENT_QUOTES) ?>" class="week-navigation__arrow">&lsaquo;</a>
                    <div class="week-navigation__days">
                        <?php foreach ($weekDays as $day): ?>
                            <a href="?date=<?= htmlspecialchars($day, ENT_QUOTES) ?>" class="week-navigation__day <?= $day === $date ? 'week-navigation__day--active' : '' ?>"><?= htmlspecialchars(date('D j', strtotime($day)), ENT_QUOTES) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <a href="?date=<?= htmlspecialchars($nextWeek, ENT_QUOTES) ?>" class="week-navigation__arrow">&rsaquo;</a>
                </div>

                <form method="post" class="schedule-form">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($editor['id'] ?? ''), ENT_QUOTES) ?>">
                    <input type="hidden" name="movie_id" id="movieId" value="<?= htmlspecialchars((string) ($editor['movie_id'] ?? ''), ENT_QUOTES) ?>">

                    <div class="schedule-picker">
                        <label class="form-label">Movie</label>
                        <input type="text" id="movieSearch" autocomplete="off" placeholder="Search by title&hellip;" value="<?= htmlspecialchars($editor['movie_title'] ?? '', ENT_QUOTES) ?>" class="form-input">
                        <div id="movieResults" class="schedule-picker__results is-hidden"></div>
                    </div>

                    <div class="form-field">
                        <label class="form-label">Screen</label>
                        <select name="screen_id" required class="form-input">
                            <?php foreach ($screens as $screen): ?>
                                <option value="<?= (int) $screen['id'] ?>" <?= (($editor['screen_id'] ?? null) == $screen['id']) ? 'selected' : '' ?>><?= htmlspecialchars(screenDisplayName($screen['type']), ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label class="form-label">Time</label>
                        <input type="time" name="show_time" value="<?= htmlspecialchars($editor['show_time'] ?? '18:00', ENT_QUOTES) ?>" required class="form-input">
                    </div>

                    <button type="submit" class="button">Save</button>
                    <?php if ($editor): ?>
                        <a href="?date=<?= htmlspecialchars($date, ENT_QUOTES) ?>" class="schedule-form__cancel">Cancel</a>
                    <?php endif; ?>
                </form>

                <?php if ($editor): ?>
                    <form method="post" onsubmit="return confirm('Delete this showing?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $editor['id'] ?>">
                        <button type="submit" class="danger-button">Delete this showing</button>
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
                resultsBox.classList.add('is-hidden');
                resultsBox.innerHTML = '';
                return;
            }

            const matches = movies.filter(m => m.title.toLowerCase().includes(term)).slice(0, 8);

            resultsBox.innerHTML = matches.map(m =>
                `<button type="button" data-id="${m.id}" data-title="${m.title.replace(/"/g, '&quot;')}" class="schedule-picker__result">${m.title}</button>`
            ).join('') || '<p class="schedule-picker__empty">No matches</p>';

            resultsBox.classList.remove('is-hidden');
        });

        resultsBox.addEventListener('click', function (event) {
            const button = event.target.closest('button[data-id]');
            if (!button) {
                return;
            }

            movieIdInput.value = button.dataset.id;
            searchInput.value = button.dataset.title;
            resultsBox.classList.add('is-hidden');
            resultsBox.innerHTML = '';
        });
    </script>

    <script src="../assets/js/schedule-drag.js"></script>
    <script>
        initScheduleDrag(<?= json_encode($date) ?>, <?= $openingHour ?>, <?= ($closingHour - $openingHour) * 60 ?>);
    </script>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
