<?php
// Central place for env configuration (DB, TMDB, Stripe, etc. as they're added).

return [
    // enable/disable automatic scheduling, duplicating current week two weeks ahead.
    // While false, run DELETE FROM showings; to clear the schedule before manually populating it.
    'auto_schedule_enabled' => true,
    'tmdb' => [
        'read_token' => getenv('TMDB_READ_TOKEN') ?: '',
    ],
    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'name' => getenv('DB_NAME') ?: '',
        'user' => getenv('DB_USER') ?: '',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],
    'mail' => [
        'host' => getenv('MAIL_HOST') ?: '',
        'port' => (int) (getenv('MAIL_PORT') ?: 587),
        'user' => getenv('MAIL_USER') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'from' => getenv('MAIL_FROM') ?: '',
        'owner' => getenv('MAIL_OWNER') ?: '',
    ],
];
