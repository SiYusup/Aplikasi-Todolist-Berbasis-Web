<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Controller;

use UCrazy\AplikasiTodolist\Middleware\AuthMiddleware;

class PageController
{
    private static function view(string $page, array $data = []): void
    {
        $user = AuthMiddleware::user();
        extract($data);
        $content = __DIR__ . '/../View/pages/' . $page . '.php';
        require __DIR__ . '/../View/layout.php';
    }

    public static function guard(): ?object
    {
        $user = AuthMiddleware::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }
        return $user;
    }

    public function home(): void
    {
        $user = self::guard();
        self::view('home', ['user' => $user, 'title' => 'Dashboard']);
    }

    public function tasks(): void
    {
        $user = self::guard();
        self::view('tasks', ['user' => $user, 'title' => 'Tugas']);
    }

    public function history(): void
    {
        $user = self::guard();
        self::view('history', ['user' => $user, 'title' => 'History']);
    }

    public function categories(): void
    {
        $user = self::guard();
        self::view('categories', ['user' => $user, 'title' => 'Kategori']);
    }

    public function settings(): void
    {
        $user = self::guard();
        self::view('settings', ['user' => $user, 'title' => 'Pengaturan']);
    }

    public function login(): void
    {
        if (AuthMiddleware::user()) {
            header('Location: /');
            exit;
        }
        self::view('login', ['title' => 'Login']);
    }

    public function register(): void
    {
        if (AuthMiddleware::user()) {
            header('Location: /');
            exit;
        }
        self::view('register', ['title' => 'Registrasi']);
    }
}
