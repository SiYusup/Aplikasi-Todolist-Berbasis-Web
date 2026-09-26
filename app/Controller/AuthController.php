<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Controller;

use UCrazy\AplikasiTodolist\Helper\Response;
use UCrazy\AplikasiTodolist\Middleware\AuthMiddleware;
use UCrazy\AplikasiTodolist\Service\UserService;

class AuthController
{
    public function __construct(private UserService $svc)
    {
    }

    public static function make(): self
    {
        return new self(UserService::make());
    }

    public function register(): void
    {
        $r = $this->svc->register(Response::input());
        if (!$r['ok']) {
            Response::fail($r['message'], $r['status'] ?? 400, $r['errors'] ?? null);
            return;
        }
        Response::json(['success' => true, 'data' => $r['user']->toPublicArray()], 201);
    }

    public function login(): void
    {
        $r = $this->svc->login(Response::input());
        if (!$r['ok']) {
            Response::fail($r['message'], $r['status'] ?? 401, $r['errors'] ?? null, $r['data'] ?? null);
            return;
        }
        AuthMiddleware::setLoginCookie($r['token'], $r['days']);
        Response::data($r['user']->toPublicArray());
    }

    public function logout(): void
    {
        $this->svc->logout($_COOKIE[AuthMiddleware::cookieName()] ?? null);
        AuthMiddleware::clearCookie();
        Response::json(['success' => true, 'message' => 'Logout berhasil']);
    }

    public function me(): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        Response::data($user->toPublicArray());
    }
}
