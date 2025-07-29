<?php
/**
 * A simple autoloader that loads classes based on their namespace.
 *
 * @param string $className The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';

    // If the file exists, require it once.
    if (file_exists($file)) {
        require_once $file; // Use require_once for robustness
    }
});