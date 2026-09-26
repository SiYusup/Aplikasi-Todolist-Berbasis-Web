<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Controller;

use UCrazy\AplikasiTodolist\Helper\Response;
use UCrazy\AplikasiTodolist\Middleware\AuthMiddleware;
use UCrazy\AplikasiTodolist\Service\TaskService;

class TaskController
{
    public function __construct(private TaskService $svc)
    {
    }

    public static function make(): self
    {
        return new self(TaskService::make());
    }

    public function list(): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        $r = $this->svc->list((int) $user->id, $_GET);
        Response::json(['success' => true, 'data' => $r['data'], 'meta' => $r['meta']]);
    }

    public function store(): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        $r = $this->svc->create((int) $user->id, Response::input());
        if (!$r['ok']) {
            Response::fail($r['message'], $r['status'] ?? 400, $r['errors'] ?? null);
            return;
        }
        Response::json(['success' => true, 'data' => $r['task']->toArray()], 201);
    }

    public function show(string $uuid): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        foreach ($this->svc->list((int) $user->id, ['limit' => 100])['data'] as $t) {
            if ($t['uuid'] === $uuid) {
                Response::data($t);
                return;
            }
        }
        Response::fail('Data tidak ditemukan', 404);
    }

    public function update(string $uuid): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        $r = $this->svc->update((int) $user->id, $uuid, Response::input());
        if (!$r['ok']) {
            Response::fail($r['message'], $r['status'] ?? 400, $r['errors'] ?? null);
            return;
        }
        Response::data($r['task']->toArray());
    }

    public function complete(string $uuid): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        $r = $this->svc->setStatus((int) $user->id, $uuid, 'completed');
        if (!$r['ok']) {
            Response::fail($r['message'], $r['status'] ?? 400);
            return;
        }
        Response::data($r['task']->toArray());
    }

    public function reopen(string $uuid): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        $r = $this->svc->setStatus((int) $user->id, $uuid, 'pending');
        if (!$r['ok']) {
            Response::fail($r['message'], $r['status'] ?? 400);
            return;
        }
        Response::data($r['task']->toArray());
    }

    public function destroy(string $uuid): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        $r = $this->svc->delete((int) $user->id, $uuid);
        if (!$r['ok']) {
            Response::fail($r['message'], $r['status'] ?? 404);
            return;
        }
        Response::json(['success' => true, 'message' => 'Tugas dihapus']);
    }

    public function reminders(): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        Response::data($this->svc->reminders((int) $user->id));
    }

    public function stats(): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        Response::data($this->svc->stats((int) $user->id));
    }
}
