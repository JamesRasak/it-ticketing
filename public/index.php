<?php

declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

use App\Router;
use App\Controllers\AuthController;
use App\Controllers\TicketController;

//require_once __DIR__ . '/../src/Router.php';

$router = new Router();

$router->get('/', [TicketController::class, 'dashboard']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/tickets', [TicketController::class, 'index']);
$router->get('/tickets/create', [TicketController::class, 'create']);
$router->post('/tickets/create', [TicketController::class, 'store']);
$router->get('/tickets/(?P<id>\\d+)', [TicketController::class, 'show']);
$router->post('/tickets/(?P<id>\\d+)/comment', [TicketController::class, 'comment']);
$router->post('/tickets/(?P<id>\\d+)/status', [TicketController::class, 'updateStatus']);
$router->post('/tickets/(?P<id>\\d+)/assign', [TicketController::class, 'assign']);
$router->post('/tickets/(?P<id>\\d+)/attach', [TicketController::class, 'attach']);

$router->dispatch();
