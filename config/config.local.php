<?php

return [
    // enable/disable automatic scheduling
    'auto_schedule_enabled' => true,

    'site_url' => rtrim(getenv('SITE_URL') ?: 'http://localhost:8080', '/'),
    'tmdb' => [
        'read_token' => getenv('TMDB_READ_TOKEN') ?: '',
    ],
    'stripe' => [
        'secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
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
