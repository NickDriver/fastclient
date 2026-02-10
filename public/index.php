<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use App\Router;
use App\Auth\Middleware;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\CustomerController;
use App\Controllers\CustomerNoteController;

$router = new Router();

// Public routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

// Protected routes
$router->group('', function (Router $router) {
    // Dashboard
    $router->get('/', [DashboardController::class, 'index']);
    $router->get('/dashboard', [DashboardController::class, 'index']);

    // Customers
    $router->get('/customers', [CustomerController::class, 'index']);
    $router->get('/customers/export', [CustomerController::class, 'export']);
    $router->get('/customers/import', [CustomerController::class, 'import']);
    $router->post('/customers/import', [CustomerController::class, 'processImport']);
    $router->get('/customers/create', [CustomerController::class, 'create']);
    $router->post('/customers', [CustomerController::class, 'store']);
    $router->get('/customers/{id}', [CustomerController::class, 'show']);
    $router->get('/customers/{id}/edit', [CustomerController::class, 'edit']);
    $router->put('/customers/{id}', [CustomerController::class, 'update']);
    $router->delete('/customers/{id}', [CustomerController::class, 'destroy']);
    $router->post('/customers/{id}/status', [CustomerController::class, 'updateStatus']);

    // Customer Notes
    $router->post('/customers/{id}/notes', [CustomerNoteController::class, 'store']);
    $router->put('/customers/{id}/notes/{noteId}', [CustomerNoteController::class, 'update']);
    $router->delete('/customers/{id}/notes/{noteId}', [CustomerNoteController::class, 'destroy']);
}, [Middleware::class]);

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

echo $router->dispatch($method, $uri);
