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
                // Count the hours shown on the timeline, using the company's opening and closing hours.
                $hourCount = $closingHour - $openingHour;
                ?>
                <!-- Pass the hour count to CSS so the columns match the hour labels below. -->
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
                        <!-- Submit the search text too, so it can be restored after a failed save. -->
                        <input type="text" id="movieSearch" name="movie_title" autocomplete="off" placeholder="Search by title&hellip;" value="<?= htmlspecialchars($editor['movie_title'] ?? '', ENT_QUOTES) ?>" class="form-input">
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

    <script>
        // Type-to-search movie picker: filters the catalog client-side (same trick as the
        // catalog filter on the Movies admin page) instead of a <select> with hundreds of options.
        // json_encode turns the PHP movie list into data JavaScript can read.
        // These flags safely encode <, >, &, and quotes, so a title cannot break out of this script.
        const movies = <?= json_encode(array_map(fn($m) => ['id' => (int) $m['id'], 'title' => $m['title']], $movies), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const searchInput = document.getElementById('movieSearch');
        const resultsBox = document.getElementById('movieResults');
        const movieIdInput = document.getElementById('movieId');

        searchInput.addEventListener('input', function () {
            movieIdInput.value = '';
            const term = this.value.trim().toLowerCase();

            if (term === '') {
                resultsBox.classList.add('is-hidden');
                // Remove all old results when the search field is empty.
                resultsBox.replaceChildren();
                return;
            }

            const matches = movies.filter(m => m.title.toLowerCase().includes(term)).slice(0, 8);

            // Clear the previous results before adding the new matches.
            resultsBox.replaceChildren();
            // Create one search-result button for each matching movie.
            for (const movie of matches) {
                // Create a real button instead of building an HTML string from the movie title.
                const button = document.createElement('button');
                // Clicking a result should select a movie, not submit the form.
                button.type = 'button';
                // Store the movie ID and title on the button so the click handler below can read them.
                button.dataset.id = movie.id;
                button.dataset.title = movie.title;
                // Use the existing CSS class to keep the same appearance.
                button.className = 'schedule-picker__result';
                // Display the title as plain text. Even a title containing HTML will not run as code.
                button.textContent = movie.title;
                // Add the finished button to the visible list of results.
                resultsBox.append(button);
            }

            // If nothing matched, show a message instead of an empty results box.
            if (matches.length === 0) {
                const message = document.createElement('p');
                message.className = 'schedule-picker__empty';
                message.textContent = 'No matches';
                resultsBox.append(message);
            }

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
            // The movie has been selected, so remove the search-result buttons.
            resultsBox.replaceChildren();
        });
    </script>

    <script src="/assets/js/schedule-drag.js"></script>
    <script>
        initScheduleDrag(<?= json_encode($date) ?>, <?= $openingHour ?>, <?= ($closingHour - $openingHour) * 60 ?>);
    </script>

    <?php require __DIR__ . '/../../components/footer.php'; ?>
    <script src="/assets/js/confirm-dialog.js"></script>
</body>
</html>
