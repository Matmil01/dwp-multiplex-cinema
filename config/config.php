<?php
// Picks the settings file: config.production.php exists only on the live server, so everywhere else Docker's config.local.php is used.
$production = __DIR__ . '/config.production.php';

return require is_file($production) ? $production : __DIR__ . '/config.local.php';
