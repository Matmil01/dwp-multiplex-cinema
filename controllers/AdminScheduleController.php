<?php

class AdminScheduleController
{
    // Runs before every action on this page, including the JSON endpoints.
    // (A constructor runs automatically when `new AdminScheduleController()` is created in
    // public/index.php, which happens before any of the functions below is called.)
    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';
        if ($config['auto_schedule_enabled']) {
            AutoSchedule::ensureBuffer();
        }
    }

    public function index(string $date, array $query): void
    {
        $this->showPage($date, null, null, $query);
    }

    public function handlePost(string $date, array $post, array $query): void
    {
        $action = $post['action'] ?? '';

        if ($action === 'save') {
            $this->save($date, $post);
        } elseif ($action === 'delete') {
            $this->delete($date, (int) ($post['id'] ?? 0));
        } elseif ($action === 'move') {
            $this->move($post);
        } elseif ($action === 'duplicate') {
            $this->duplicate($post);
        } else {
            $this->index($date, $query);
        }
    }

    // The form: a movie (picked from a search list), a screen, and a time for the page's $date.
    public function save(string $date, array $input): void
    {
        $saveError = null;

        if ((int) ($input['movie_id'] ?? 0) === 0) {
            $saveError = 'Pick a movie from the search results before saving.';
        } else {
            try {
                // The form only has a time ("20:00"); glue the page's date in front to make the
                // full "2026-10-31 20:00:00" the database column expects.
                Showing::save([
                    'id' => ($input['id'] ?? '') !== '' ? (int) $input['id'] : null,
                    'movie_id' => (int) $input['movie_id'],
                    'screen_id' => (int) ($input['screen_id'] ?? 0),
                    'show_datetime' => $date . ' ' . ($input['show_time'] ?? '') . ':00',
                ]);
                header('Location: /admin/schedule?date=' . urlencode($date) . '&saved=1');
                exit;
            } catch (Throwable $e) {
                $saveError = $e->getMessage();
            }
        }

        // If saving failed, refill the form with what you submitted instead of the old saved values.
        $editor = [
            'id' => ($input['id'] ?? '') !== '' ? (int) $input['id'] : null,
            'movie_id' => (int) ($input['movie_id'] ?? 0),
            'movie_title' => $input['movie_title'] ?? '',
            'screen_id' => (int) ($input['screen_id'] ?? 0),
            'show_time' => $input['show_time'] ?? '',
        ];

        $this->showPage($date, $saveError, $editor, []);
    }

    public function delete(string $date, int $id): void
    {
        try {
            Showing::delete($id);
        } catch (RuntimeException $e) {
            $this->showPage($date, $e->getMessage(), null, []);
            return;
        }

        header('Location: /admin/schedule?date=' . urlencode($date) . '&deleted=1');
        exit;
    }

    // Drag-and-drop grid endpoint: same save path as the form, just responds with JSON instead of redirecting.
    // Content-Type tells the browser "this response is JSON data, not an HTML page".
    // json_encode() turns a PHP list into JSON text, e.g. ['ok' => true] -> {"ok":true}.
    // Status 422 ("unprocessable") on failure lets the script tell success from failure
    // without even reading the body.
    public function move(array $input): void
    {
        header('Content-Type: application/json');
        try {
            Showing::save([
                'id' => (int) ($input['id'] ?? 0),
                'movie_id' => (int) ($input['movie_id'] ?? 0),
                'screen_id' => (int) ($input['screen_id'] ?? 0),
                'show_datetime' => $input['show_datetime'] ?? '',
            ]);
            echo json_encode(['ok' => true]);
        } catch (Throwable $e) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // Duplicate-on-timeline endpoint: same movie/screen as the clicked block, placed right after it ends.
    public function duplicate(array $input): void
    {
        header('Content-Type: application/json');
        try {
            $newId = Showing::save([
                'id' => null,
                'movie_id' => (int) ($input['movie_id'] ?? 0),
                'screen_id' => (int) ($input['screen_id'] ?? 0),
                'show_datetime' => $input['show_datetime'] ?? '',
            ]);
            echo json_encode(['ok' => true, 'id' => $newId]);
        } catch (Throwable $e) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    private function showPage(string $date, ?string $saveError, ?array $editor, array $query): void
    {
        // ?edit_id=5 in the address: pre-fill the form with that showing (unless a failed save
        // already provided $editor with the admin's unsaved input).
        if ($editor === null && isset($query['edit_id'])) {
            $editor = Showing::find((int) $query['edit_id']);
            if ($editor) {
                $editor['show_time'] = date('H:i', strtotime($editor['show_datetime']));
            }
        }

        // The grid spans the cinema's opening hours. substr("10:00:00", 0, 2) = "10" -> 10.
        $company = CompanyInfo::find();
        $openingHour = (int) substr($company['opening_time'], 0, 2);
        // Rounds a closing time with leftover minutes (e.g. 23:59) up to the next full hour column.
        $closingHour = (int) substr($company['closing_time'], 0, 2) + ((int) substr($company['closing_time'], 3, 2) > 0 ? 1 : 0);

        $movies = Movie::all();
        $screens = Showing::screens();

        // For every showing on this date, work out where its block sits on the timeline and
        // sort it into its screen's row. $showingsByScreen[screen id] = list of showings.
        $showingsByScreen = [];
        foreach (Showing::forDate($date) as $showing) {
            $startTimestamp = strtotime($showing['show_datetime']);
            // A movie with no runtime set still needs a visible block; assume 90 minutes for drawing.
            $duration = $showing['duration_minutes'] ?: 90;

            // [$a, $b] = f() unpacks a two-item list into two variables.
            [$showing['left'], $showing['width']] = $this->timelineStyle($showing['show_datetime'], $duration, $openingHour, $closingHour);
            $showing['minutes'] = $this->timelineMinutes($showing['show_datetime'], $openingHour);
            $showing['duration'] = (int) $duration;
            $showing['start_time'] = date('H:i', $startTimestamp);
            // Timestamps are in seconds, so minutes * 60.
            $showing['end_time'] = date('H:i', $startTimestamp + $duration * 60);

            $showingsByScreen[$showing['screen_id']][] = $showing;
        }

        // The week strip above the grid: Monday..Sunday of the week $date falls in, plus
        // prev/next week links. strtotime() understands phrases like 'monday this week'.
        $weekStart = strtotime('monday this week', strtotime($date));
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = date('Y-m-d', strtotime("+$i days", $weekStart));
        }
        $prevWeek = date('Y-m-d', strtotime('-7 days', strtotime($date)));
        $nextWeek = date('Y-m-d', strtotime('+7 days', strtotime($date)));

        $justSaved = isset($query['saved']);
        $justDeleted = isset($query['deleted']);

        require __DIR__ . '/../views/admin/schedule.php';
    }

    // Grid covers the company's opening-closing window; returns a block's left/width as % of that span.
    // Example with 10:00-24:00 (840 minutes): a 20:00 showing starts 600 minutes in -> left = 600/840
    // = 71%; a 120-minute film -> width = 120/840 = 14%. The view puts these straight into CSS.
    // max()/min() clamp the numbers so a block never leaves the grid and is never thinner than 3%.
    private function timelineStyle(string $showDatetime, int $durationMinutes, int $openingHour, int $closingHour): array
    {
        $startOfSpan = strtotime(date('Y-m-d', strtotime($showDatetime)) . " $openingHour:00");
        $minutesIn = (strtotime($showDatetime) - $startOfSpan) / 60;
        $span = ($closingHour - $openingHour) * 60;

        $left = max(0, min(100, $minutesIn / $span * 100));
        $width = max(3, min(100 - $left, $durationMinutes / $span * 100));

        return [$left, $width];
    }

    // Raw (unclamped) minutes since the grid's opening-hour start, for the drag script's snapping math.
    private function timelineMinutes(string $showDatetime, int $openingHour): int
    {
        $startOfSpan = strtotime(date('Y-m-d', strtotime($showDatetime)) . " $openingHour:00");

        return (int) round((strtotime($showDatetime) - $startOfSpan) / 60);
    }
}
