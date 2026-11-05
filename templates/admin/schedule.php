<?php
require __DIR__ . '/../../components/screen-format.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - Admin - Untitled Cinema</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/nav-footer.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="site-body">
    <header class="site-header">
        <?php require __DIR__ . '/../../components/admin-navbar.php'; ?>
    </header>

    <main class="site-main">
        <section>
            <div class="page-heading">
                <h2 class="section-title">Schedule</h2>
                <p class="schedule-board__month"><?= htmlspecialchars(date('F Y', strtotime($date)), ENT_QUOTES) ?></p>
            </div>

            <div class="schedule-board-wrap">
                <?php
                $hourCount = $closingHour - $openingHour;
                ?>
                <div class="schedule-board" style="--schedule-hour-count: <?= $hourCount ?>;">
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
                                        <div class="schedule-board__showing"
                                           style="left: <?= $showing['left'] ?>%; width: <?= $showing['width'] ?>%;"
                                           title="<?= htmlspecialchars("{$showing['start_time']}\u{2013}{$showing['end_time']} \u{00b7} {$showing['movie_title']}", ENT_QUOTES) ?>"
                                           data-id="<?= (int) $showing['id'] ?>"
                                           data-movie-id="<?= (int) $showing['movie_id'] ?>"
                                           data-minutes="<?= $showing['minutes'] ?>"
                                           data-duration="<?= $showing['duration'] ?>"
                                           data-edit-href="?date=<?= htmlspecialchars($date, ENT_QUOTES) ?>&edit_id=<?= (int) $showing['id'] ?>">
                                            <span class="schedule-board__showing-label">
                                                <span class="schedule-board__showing-time"><?= htmlspecialchars($showing['start_time'], ENT_QUOTES) ?>&ndash;<?= htmlspecialchars($showing['end_time'], ENT_QUOTES) ?></span>
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
            <h3 class="admin-subheading"><?= !empty($editor['id']) ? 'Edit Showing' : 'Add Showing' ?></h3>

            <?php if ($justSaved): ?>
                <p class="notice notice--success">Showing saved.</p>
            <?php elseif ($justDeleted): ?>
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
                        <input type="text" id="movieSearch" name="movie_title" autocomplete="off" placeholder="Search by title&hellip;" value="<?= htmlspecialchars($editor['movie_title'] ?? '', ENT_QUOTES) ?>" data-movies="<?= htmlspecialchars(json_encode(array_map(fn($m) => ['id' => (int) $m['id'], 'title' => $m['title']], $movies)), ENT_QUOTES) ?>" class="form-input">
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

                    <button type="submit" class="base-button">Save</button>
                    <?php if ($editor): ?>
                        <a href="?date=<?= htmlspecialchars($date, ENT_QUOTES) ?>" class="schedule-form__cancel">Cancel</a>
                    <?php endif; ?>
                </form>

                <?php if (!empty($editor['id'])): ?>
                    <form method="post" data-confirm="Delete this showing?" data-confirm-label="Delete">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $editor['id'] ?>">
                        <button type="submit" class="danger-button">Delete this showing</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="/assets/js/movie-picker.js"></script>
    <script src="/assets/js/schedule-drag.js"></script>
    <script>
        // Start the drag behaviour, handing it the three numbers it needs from PHP: the grid's
        // date, the opening hour, and the total span in minutes (e.g. 10:00-24:00 = 840).
        initScheduleDrag(<?= json_encode($date) ?>, <?= $openingHour ?>, <?= ($closingHour - $openingHour) * 60 ?>);
    </script>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
    <script src="/assets/js/confirm-dialog.js"></script>
</body>
</html>
