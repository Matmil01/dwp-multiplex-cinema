<?php
// Loads a class's file the first time it is used, by looking for ClassName.php in these folders.
spl_autoload_register(function (string $class): void {
    foreach (['controllers', 'models', 'classes', 'demo'] as $folder) {
        $file = __DIR__ . "/../$folder/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});
