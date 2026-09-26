<?php

declare(strict_types=1);

// Static assets passthrough when using `php -S -t public public/index.php`
if (PHP_SAPI === 'cli-server') {
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($reqPath !== '/' && is_file(__DIR__ . $reqPath)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;
use UCrazy\AplikasiTodolist\Controller\AuthController;
use UCrazy\AplikasiTodolist\Controller\CategoryController;
use UCrazy\AplikasiTodolist\Controller\PageController;
use UCrazy\AplikasiTodolist\Controller\TaskController;
use UCrazy\AplikasiTodolist\Controller\UserController;
use UCrazy\AplikasiTodolist\Helper\Response;

$router = new Router();
$router->setBasePath('');
$UUID = '[0-9a-fA-F-]{36}';

$pages = new PageController();
$auth = AuthController::make();
$tasks = TaskController::make();
$cats = CategoryController::make();
$users = UserController::make();

// ---------- Web (SSR + Alpine/Axios) ----------
$router->get('/', fn() => $pages->home());
$router->get('/tasks', fn() => $pages->tasks());
$router->get('/history', fn() => $pages->history());
$router->get('/categories', fn() => $pages->categories());
$router->get('/settings', fn() => $pages->settings());
$router->get('/login', fn() => $pages->login());
$router->get('/register', fn() => $pages->register());

// ---------- API: Auth ----------
$router->post('/api/auth/register', fn() => $auth->register());
$router->post('/api/auth/login', fn() => $auth->login());
$router->post('/api/auth/logout', fn() => $auth->logout());
$router->get('/api/auth/me', fn() => $auth->me());

// ---------- API: Tasks (reminders BEFORE {uuid}) ----------
$router->get('/api/tasks/reminders', fn() => $tasks->reminders());
$router->get('/api/tasks', fn() => $tasks->list());
$router->post('/api/tasks', fn() => $tasks->store());
$router->get("/api/tasks/($UUID)", fn($uuid) => $tasks->show($uuid));
$router->put("/api/tasks/($UUID)", fn($uuid) => $tasks->update($uuid));
$router->patch("/api/tasks/($UUID)/complete", fn($uuid) => $tasks->complete($uuid));
$router->patch("/api/tasks/($UUID)/reopen", fn($uuid) => $tasks->reopen($uuid));
$router->delete("/api/tasks/($UUID)", fn($uuid) => $tasks->destroy($uuid));

// ---------- API: Categories ----------
$router->get('/api/categories', fn() => $cats->index());
$router->post('/api/categories', fn() => $cats->store());
$router->get("/api/categories/($UUID)", fn($uuid) => $cats->show($uuid));
$router->put("/api/categories/($UUID)", fn($uuid) => $cats->update($uuid));
$router->delete("/api/categories/($UUID)", fn($uuid) => $cats->destroy($uuid));

// ---------- API: Dashboard + User ----------
$router->get('/api/dashboard/stats', fn() => $tasks->stats());
$router->put('/api/user/username', fn() => $users->updateUsername());
$router->put('/api/user/password', fn() => $users->updatePassword());

$router->set404(function () {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (str_starts_with($uri, '/api')) {
        Response::fail('Endpoint tidak ditemukan', 404);
        return;
    }
    http_response_code(404);
    echo '404 - Halaman tidak ditemukan';
});

$router->run();
