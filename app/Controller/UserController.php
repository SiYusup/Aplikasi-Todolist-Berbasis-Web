<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Controller;

use UCrazy\AplikasiTodolist\Helper\Response;
use UCrazy\AplikasiTodolist\Middleware\AuthMiddleware;
use UCrazy\AplikasiTodolist\Service\UserService;

class UserController
{
    public function __construct(private UserService $svc)
    {
    }

    public static function make(): self
    {
        return new self(UserService::make());
    }

    public function updateUsername(): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        $r = $this->svc->changeUsername($user, Response::input());
        if (!$r['ok']) {
            Response::fail($r['message'], $r['status'] ?? 400, $r['errors'] ?? null);
            return;
        }
        Response::data($r['user']->toPublicArray());
    }

    public function updatePassword(): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        $r = $this->svc->changePassword($user, Response::input());
        if (!$r['ok']) {
            Response::fail($r['message'], $r['status'] ?? 400, $r['errors'] ?? null);
            return;
        }
        Response::json(['success' => true, 'message' => 'Password diperbarui']);
    }
}
