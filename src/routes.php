<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\CatalogController;
use App\Controllers\AdminController;
use App\Controllers\PlanController;
use App\Controllers\Api\PlanApiController;

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

$router->add('GET', '/bugun', [PlanController::class, 'today'], ['auth', 'role:student']);
$router->add('GET', '/plan/yeni', [PlanController::class, 'create'], ['auth', 'role:student']);
$router->add('GET', '/plan/duzenle', [PlanController::class, 'edit'], ['auth', 'role:student']);
$router->add('POST', '/plan', [PlanController::class, 'store'], ['auth', 'role:student', 'csrf']);
$router->add('POST', '/plan/guncelle', [PlanController::class, 'update'], ['auth', 'role:student', 'csrf']);
$router->add('POST', '/plan/sil', [PlanController::class, 'delete'], ['auth', 'role:student', 'csrf']);

$router->add('POST', '/plan/durum', [PlanController::class, 'status'], ['auth', 'role:student', 'csrf']);
foreach (['GET' => 'index', 'POST' => 'create'] as $method => $action) {
    $router->add($method, '/api/plans', [PlanApiController::class, $action],
        $method === 'GET' ? ['auth', 'role:student'] : ['auth', 'role:student', 'csrf']);
}
foreach (['GET' => 'show', 'PUT' => 'update', 'DELETE' => 'delete'] as $method => $action) {
    $router->add($method, '/api/plans/{id}', [PlanApiController::class, $action],
        $method === 'GET' ? ['auth', 'role:student'] : ['auth', 'role:student', 'csrf']);
}
$router->add('PATCH', '/api/plans/{id}/status', [PlanApiController::class, 'status'],
    ['auth', 'role:student', 'csrf']);
