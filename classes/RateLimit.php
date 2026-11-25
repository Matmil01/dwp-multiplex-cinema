<?php

// Counts recent attempts per visitor IP in the rate_limits table, for forms that can be spammed or guessed.
class RateLimit
{
    // Refuses once this IP has made $max attempts at $action within the window; otherwise records this attempt.
    // Refused attempts are not recorded, so the table stays small and the block ends when old attempts age out.
    public static function allows(string $action, int $max, int $windowSeconds): bool
    {
        $db = Db::connection();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $since = date('Y-m-d H:i:s', time() - $windowSeconds);

        $stmt = $db->prepare('SELECT COUNT(*) FROM rate_limits WHERE action = ? AND ip = ? AND attempted_at > ?');
        $stmt->execute([$action, $ip, $since]);

        if ((int) $stmt->fetchColumn() >= $max) {
            return false;
        }

        $db->prepare('INSERT INTO rate_limits (ip, action) VALUES (?, ?)')->execute([$ip, $action]);
        // Housekeeping: nothing older than a day is ever needed.
        $db->prepare('DELETE FROM rate_limits WHERE attempted_at < ?')->execute([date('Y-m-d H:i:s', time() - 86400)]);

        return true;
    }
}
