<?php

// Libraries installed by Composer (PHPMailer, Stripe) live in vendor/.
// Composer is PHP's package manager (like npm for JavaScript). It downloaded those libraries
// into vendor/ and wrote its own autoloader for them, which we load here.
require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function (string $class): void {
    foreach (['controllers', 'models', 'classes', 'demo'] as $folder) {
        $file = __DIR__ . "/../$folder/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});
