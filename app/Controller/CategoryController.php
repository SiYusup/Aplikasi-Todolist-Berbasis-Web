<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Controller;

use UCrazy\AplikasiTodolist\Helper\Response;
use UCrazy\AplikasiTodolist\Middleware\AuthMiddleware;
use UCrazy\AplikasiTodolist\Service\CategoryService;

class CategoryController
{
    public function __construct(private CategoryService $svc)
    {
    }

    public static function make(): self
    {
        return new self(CategoryService::make());
    }

    public function index(): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        Response::data($this->svc->list((int) $user->id));
    }

    public function store(): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        $r = $this->svc->create((int) $user->id, Response::input());
        if (!$r['ok']) {
            Response::fail($r['message'], 400, $r['errors'] ?? null);
            return;
        }
        Response::json(['success' => true, 'data' => $r['category']->toArray()], 201);
    }

    public function show(string $uuid): void
    {
        $user = AuthMiddleware::requireApi();
        if (!$user) {
            return;
        }
        foreach ($this->svc->list((int) $user->id) as $c) {
            if ($c['uuid'] === $uuid) {
                Response::data($c);
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
        Response::data($r['category']->toArray());
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
        Response::json(['success' => true, 'message' => 'Kategori dihapus']);
    }
}
