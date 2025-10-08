<?php

declare(strict_types=1);

namespace App;

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    // If you're on PHP 8+, this union type is fine. If you're on PHP 7.x, see note below.
    public function get(string $pattern, callable|array $handler): void
    {
        $this->routes['GET'][$pattern] = $handler;
    }

    public function post(string $pattern, callable|array $handler): void
    {
        $this->routes['POST'][$pattern] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Full URL path, e.g., "/it-ticketing/public/tickets/1"
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Compute base path from SCRIPT_NAME, e.g., "/it-ticketing/public/index.php" -> "/it-ticketing/public"
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        // Strip base path (only if not empty or root) so routes see "/tickets/1" instead of "/it-ticketing/public/tickets/1"
        if ($basePath !== '' && $basePath !== '/' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $pattern => $handler) {
            $regex = '#^' . $pattern . '$#';
            if (preg_match($regex, $uri, $matches)) {

                // CSRF check for POST
                if ($method === 'POST') {
                    \App\csrf_check();
                }

                // Drop numeric keys from matches
                foreach ($matches as $k => $v) {
                    if (is_int($k)) unset($matches[$k]);
                }

                // ✅ Cast numeric strings to int so strict-typed controllers accept them
                foreach ($matches as $k => $v) {
                    if (is_string($v) && preg_match('/^-?\d+$/', $v)) {
                        $matches[$k] = (int) $v;
                    }
                }

                // Invoke handler (keep dispatch(): void – don't return a value)
                if (is_array($handler)) {
                    [$class, $fn] = $handler;
                    $obj = new $class();
                    $result = $obj->$fn(...array_values($matches));
                } else {
                    $result = $handler(...array_values($matches));
                }

                if ($result !== null) {
                    echo $result;
                }
                return; // do not return a value from a void function
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}
