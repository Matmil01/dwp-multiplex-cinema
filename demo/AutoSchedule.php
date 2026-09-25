<?php

require_once __DIR__ . '/../classes/Db.php';

// Keeps the schedule from ever running dry if enabled in config.php
// Repeats the most recent week in the showings table.
class AutoSchedule
{
    private const BUFFER_DAYS = 14;
    private const MAX_ITERATIONS = 6; // safety cap so a bad run can't loop forever

    public static function ensureBuffer(): void
    {
        $db = Db::connection();
        $threshold = (new DateTime())->modify('+' . self::BUFFER_DAYS . ' days');

        for ($i = 0; $i < self::MAX_ITERATIONS; $i++) {
            $furthest = $db->query('SELECT MAX(show_datetime) FROM showings')->fetchColumn();

            if ($furthest === null || new DateTime($furthest) >= $threshold) {
                return;
            }

            self::copyWeekForward($db, $furthest);
        }
    }

    private static function copyWeekForward(PDO $db, string $furthestDatetime): void
    {
        $weekStart = (new DateTime($furthestDatetime))->modify('-7 days')->format('Y-m-d H:i:s');

        $stmt = $db->prepare(
            'SELECT movie_id, screen_id, show_datetime FROM showings
             WHERE show_datetime > ? AND show_datetime <= ?'
        );
        $stmt->execute([$weekStart, $furthestDatetime]);

        $insert = $db->prepare(
            'INSERT INTO showings (movie_id, screen_id, show_datetime) VALUES (?, ?, ?)'
        );

        foreach ($stmt->fetchAll() as $row) {
            $newDatetime = (new DateTime($row['show_datetime']))->modify('+7 days')->format('Y-m-d H:i:s');

            try {
                $insert->execute([$row['movie_id'], $row['screen_id'], $newDatetime]);
            } catch (PDOException $e) {
                // Slot already taken by a manually-added showing — the overlap trigger
                // rejected it, which is fine, just skip that one and keep going.
                if ($e->getCode() !== '45000') {
                    throw $e;
                }
            }
        }
    }
}
