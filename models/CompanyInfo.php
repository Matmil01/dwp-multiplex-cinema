<?php

class CompanyInfo
{
    // The single row. fetch() (not fetchAll) because there is only one.
    public static function find(): array
    {
        return Db::connection()->query('SELECT * FROM company_info WHERE id = 1')->fetch();
    }

    // Refuses to save if any future showing falls outside the new opening/closing window,
    // rather than silently leaving the schedule inconsistent with the published hours.
    // Example: closing time changed to 22:00 while a showing is scheduled for 23:00 tomorrow.
    public static function save(array $input): void
    {
        $db = Db::connection();

        // TIME(...) keeps only the clock part of a date+time, so "2026-11-01 23:00:00" becomes
        // "23:00:00" and can be compared with the opening/closing times.
        // Finds future showings that start before opening OR at/after closing.
        $stmt = $db->prepare(
            'SELECT m.title, s.show_datetime
             FROM showings s
             JOIN movies m ON m.id = s.movie_id
             WHERE s.show_datetime >= NOW()
               AND (TIME(s.show_datetime) < ? OR TIME(s.show_datetime) >= ?)
             ORDER BY s.show_datetime'
        );
        $stmt->execute([$input['opening_time'], $input['closing_time']]);
        $conflicts = $stmt->fetchAll();

        // Any conflicts: build a readable list like "Alien (Nov 1, 23:00), Heat (Nov 2, 23:15)"
        // and refuse with that as the message. array_map turns each row into one such text;
        // implode joins them with commas.
        if ($conflicts) {
            $list = implode(', ', array_map(
                fn($row) => "{$row['title']} (" . date('M j, H:i', strtotime($row['show_datetime'])) . ')',
                $conflicts
            ));
            throw new RuntimeException("These upcoming showings fall outside the new hours: $list");
        }

        // No conflicts: save all six fields in one UPDATE.
        $db->prepare(
            'UPDATE company_info SET tagline = ?, description = ?, opening_time = ?, closing_time = ?, address = ?, phone = ? WHERE id = 1'
        )->execute([
            $input['tagline'],
            $input['description'],
            $input['opening_time'],
            $input['closing_time'],
            $input['address'],
            $input['phone'],
        ]);
    }
}
