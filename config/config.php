<?php

// Picks the settings file: config.production.php exists only on the live server, falls back to config.local.php for Docker.
$production = __DIR__ . '/config.production.php';

return require is_file($production) ? $production : __DIR__ . '/config.local.php';
