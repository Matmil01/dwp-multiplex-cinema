<?php

class AdminScheduleController
{
    // Runs before every action on this page, including the JSON endpoints.
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

    public function save(string $date, array $input): void
    {
        $saveError = null;

        if ((int) ($input['movie_id'] ?? 0) === 0) {
            $saveError = 'Pick a movie from the search results before saving.';
        } else {
            try {
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
        if ($editor === null && isset($query['edit_id'])) {
            $editor = Showing::find((int) $query['edit_id']);
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
            $startTimestamp = strtotime($showing['show_datetime']);
            $duration = $showing['duration_minutes'] ?: 90;

            [$showing['left'], $showing['width']] = $this->timelineStyle($showing['show_datetime'], $duration, $openingHour, $closingHour);
            $showing['minutes'] = $this->timelineMinutes($showing['show_datetime'], $openingHour);
            $showing['duration'] = (int) $duration;
            $showing['start_time'] = date('H:i', $startTimestamp);
            $showing['end_time'] = date('H:i', $startTimestamp + $duration * 60);

            $showingsByScreen[$showing['screen_id']][] = $showing;
        }

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
