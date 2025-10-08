<?php

declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

echo "<pre>";
echo "PHP_VERSION: ", PHP_VERSION, PHP_EOL;
echo "Router file: ", (file_exists(__DIR__ . '/../src/Router.php') ? "FOUND" : "MISSING"), PHP_EOL;
echo "TicketController file: ", (file_exists(__DIR__ . '/../src/Controllers/TicketController.php') ? "FOUND" : "MISSING"), PHP_EOL;
echo "AuthController file: ", (file_exists(__DIR__ . '/../src/Controllers/AuthController.php') ? "FOUND" : "MISSING"), PHP_EOL;

echo "class_exists(App\\Router): ", (class_exists('App\\Router') ? "YES" : "NO"), PHP_EOL;
echo "class_exists(App\\Controllers\\TicketController): ", (class_exists('App\\Controllers\\TicketController') ? "YES" : "NO"), PHP_EOL;
echo "class_exists(App\\Controllers\\AuthController): ", (class_exists('App\\Controllers\\AuthController') ? "YES" : "NO"), PHP_EOL;
echo "</pre>";
