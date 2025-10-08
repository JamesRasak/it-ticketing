<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    // Project namespace
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';

    // Only handle our namespace
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    // Map e.g. App\Router -> src/Router.php
    $relative_class = substr($class, strlen($prefix));
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helpers + .env
require __DIR__ . '/helpers.php';
\App\env_load(__DIR__ . '/../.env');

// Optional: tighten session flags when running under HTTPS
if (PHP_SAPI !== 'cli') {
    @ini_set('session.cookie_httponly', '1');
    if (!empty($_SERVER['HTTPS']) || (getenv('APP_URL') && str_starts_with(getenv('APP_URL'), 'https://'))) {
        @ini_set('session.cookie_secure', '1');
    }
    @ini_set('session.use_strict_mode', '1');
}
