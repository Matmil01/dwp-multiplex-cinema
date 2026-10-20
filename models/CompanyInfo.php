<?php

class CompanyInfo
{
    public static function find(): array
    {
        return Db::connection()->query('SELECT * FROM company_info WHERE id = 1')->fetch();
    }

    // Refuses to save if any future showing falls outside the new opening/closing window,
    // rather than silently leaving the schedule inconsistent with the published hours.
    public static function save(array $input): void
    {
        $db = Db::connection();

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

        if ($conflicts) {
            $list = implode(', ', array_map(
                fn($row) => "{$row['title']} (" . date('M j, H:i', strtotime($row['show_datetime'])) . ')',
                $conflicts
            ));
            throw new RuntimeException("These upcoming showings fall outside the new hours: $list");
        }

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
