<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\CatalogController;
use App\Controllers\AdminController;

$router->add('GET', '/', [HomeController::class, 'index']);
foreach (['GET', 'POST'] as $method) {
    $middleware = $method === 'POST' ? ['csrf', 'guest'] : ['guest'];
    $router->add($method, '/kayit', [AuthController::class, 'register'], $middleware);
    $router->add($method, '/giris', [AuthController::class, 'login'], $middleware);
}
$router->add('POST', '/cikis', [AuthController::class, 'logout'], ['csrf', 'auth']);
$router->add('GET', '/katalog', [CatalogController::class, 'index'], ['auth']);
$router->add('GET', '/api/konular', [CatalogController::class, 'topicsJson'], ['auth']);
$router->add('GET', '/api/subjects', [CatalogController::class, 'subjectsJson'], ['auth', 'role:student']);
$router->add('GET', '/yonetim', [AdminController::class, 'index'], ['auth', 'role:admin']);
