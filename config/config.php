<?php
// Central place for environment-based configuration (DB, TMDB, Stripe, etc. as they're added).

return [
    'tmdb' => [
        'read_token' => getenv('TMDB_READ_TOKEN') ?: '',
    ],
    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'name' => getenv('DB_NAME') ?: '',
        'user' => getenv('DB_USER') ?: '',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],
];
