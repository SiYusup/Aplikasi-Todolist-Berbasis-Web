<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Service;

use UCrazy\AplikasiTodolist\Config\Database;
use UCrazy\AplikasiTodolist\Repository\CategoryRepository;
use Valitron\Validator;

class CategoryService
{
    public function __construct(private CategoryRepository $cats)
    {
    }

    public static function make(): self
    {
        return new self(new CategoryRepository(Database::connection()));
    }

    public function list(int $userId): array
    {
        return array_map(fn($c) => $c->toArray(), $this->cats->allByUser($userId));
    }

    public function create(int $userId, array $in): array
    {
        $v = new Validator($in);
        $v->rule('required', 'name');
        $v->rule('lengthMax', 'name', 50);
        $v->rule('lengthMax', 'color', 20);
        if (!$v->validate()) {
            return ['ok' => false, 'message' => 'Validasi gagal', 'errors' => $this->flat($v->errors())];
        }
        $cat = $this->cats->create($userId, trim($in['name']), trim($in['color'] ?? '#71717a') ?: '#71717a');
        return ['ok' => true, 'category' => $cat];
    }

    public function update(int $userId, string $uuid, array $in): array
    {
        $cat = $this->cats->findByUuid($uuid, $userId);
        if (!$cat) {
            return ['ok' => false, 'status' => 404, 'message' => 'Data tidak ditemukan'];
        }
        $v = new Validator($in);
        $v->rule('lengthMax', 'name', 50);
        $v->rule('lengthMax', 'color', 20);
        if (!$v->validate()) {
            return ['ok' => false, 'message' => 'Validasi gagal', 'errors' => $this->flat($v->errors())];
        }
        $data = [];
        if (isset($in['name'])) {
            $data['name'] = trim($in['name']);
        }
        if (isset($in['color'])) {
            $data['color'] = trim($in['color']);
        }
        if ($data) {
            $this->cats->update((int) $cat->id, $data);
        }
        return ['ok' => true, 'category' => $this->cats->findByUuid($uuid, $userId)];
    }

    public function delete(int $userId, string $uuid): array
    {
        $cat = $this->cats->findByUuid($uuid, $userId);
        if (!$cat) {
            return ['ok' => false, 'status' => 404, 'message' => 'Data tidak ditemukan'];
        }
        $this->cats->delete((int) $cat->id);
        return ['ok' => true];
    }

    private function flat(array $errors): array
    {
        $out = [];
        foreach ($errors as $f => $m) {
            $out[$f] = implode(', ', (array) $m);
        }
        return $out;
    }
}
