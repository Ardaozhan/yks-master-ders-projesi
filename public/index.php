<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Istanbul');

use App\Core\Middleware;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = realpath(__DIR__ . $path);
    if ($file && str_starts_with($file, __DIR__ . DIRECTORY_SEPARATOR) && is_file($file)) {
        return false;
    }
}

spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        require dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    }
});

require dirname(__DIR__) . '/src/helpers.php';
ini_set('display_errors', '0');
Session::start();

try {
    $configFile = dirname(__DIR__) . '/config/config.php';
    $config = require (is_file($configFile) ? $configFile : dirname(__DIR__) . '/config/config.example.php');
    $router = new Router($config['base_path']);
    require dirname(__DIR__) . '/src/routes.php';
    $route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    if (!$route) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $allowed = $router->allowedMethods($_SERVER['REQUEST_URI']);
        if (str_starts_with($path, rtrim($config['base_path'], '/') . '/api/')) {
            http_response_code($allowed ? 405 : 404);
            header('Content-Type: application/json; charset=utf-8');
            if ($allowed) {
                header('Allow: ' . implode(', ', $allowed));
            }
            echo json_encode(['status' => 'error', 'data' => null, 'error' => 'İstek bulunamadı.', 'errors' => null]);
            exit;
        }
        View::render('errors/404', [], 404);
        exit;
    }
    (new Middleware())->handle($route['middleware'], $_SERVER['REQUEST_URI'], $config['base_path']);
    [$class, $method] = $route['handler'];
    (new $class())->$method(...$route['params']);
} catch (Throwable $error) {
    $message = get_class($error) . ': ' . $error->getMessage()
        . ' @ ' . $error->getFile() . ':' . $error->getLine();
    error_log($message . PHP_EOL, 3, dirname(__DIR__) . '/storage/logs/app.log');
    http_response_code(500);
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $base = rtrim($config['base_path'] ?? '', '/');
    if (str_starts_with($path, $base . '/api/')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['status' => 'error', 'data' => null, 'error' => 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.',
                'errors' => null],
            JSON_UNESCAPED_UNICODE
        );
    } else {
        echo 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
    }
}
