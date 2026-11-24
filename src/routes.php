<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;

$router->add('GET', '/', [HomeController::class, 'index']);
foreach (['GET', 'POST'] as $method) {
    $middleware = $method === 'POST' ? ['csrf', 'guest'] : ['guest'];
    $router->add($method, '/kayit', [AuthController::class, 'register'], $middleware);
    $router->add($method, '/giris', [AuthController::class, 'login'], $middleware);
}
$router->add('POST', '/cikis', [AuthController::class, 'logout'], ['csrf', 'auth']);
