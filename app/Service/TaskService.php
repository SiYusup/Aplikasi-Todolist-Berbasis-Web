<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Service;

use UCrazy\AplikasiTodolist\Config\Database;
use UCrazy\AplikasiTodolist\Repository\CategoryRepository;
use UCrazy\AplikasiTodolist\Repository\TaskRepository;
use Valitron\Validator;

class TaskService
{
    public function __construct(private TaskRepository $tasks, private CategoryRepository $cats)
    {
    }

    public static function make(): self
    {
        $db = Database::connection();
        return new self(new TaskRepository($db), new CategoryRepository($db));
    }

    public function list(int $userId, array $q): array
    {
        $status = in_array($q['status'] ?? '', ['pending', 'completed'], true) ? $q['status'] : null;
        $categoryId = null;
        if (!empty($q['category_uuid'])) {
            $cat = $this->cats->findByUuid($q['category_uuid'], $userId);
            if (!$cat) {
                return ['data' => [], 'meta' => ['page' => 1, 'limit' => 20, 'total' => 0]];
            }
            $categoryId = (int) $cat->id;
        }
        $page = max(1, (int) ($q['page'] ?? 1));
        $limit = min(100, max(1, (int) ($q['limit'] ?? 20)));
        $rows = $this->tasks->allByUser($userId, $status, $categoryId, $limit, ($page - 1) * $limit);
        return [
            'data' => array_map(fn($t) => $t->toArray(), $rows),
            'meta' => ['page' => $page, 'limit' => $limit, 'total' => $this->tasks->countByUser($userId, $status)],
        ];
    }

    public function create(int $userId, array $in): array
    {
        $v = new Validator($in);
        $v->rule('required', 'title');
        $v->rule('lengthMax', 'title', 255);
        $v->rule('integer', 'reminder_minutes');
        $v->rule('min', 'reminder_minutes', 0);
        if (!$v->validate()) {
            return ['ok' => false, 'message' => 'Validasi gagal', 'errors' => $this->flat($v->errors())];
        }
        $data = [
            'title' => trim($in['title']),
            'description' => $in['description'] ?? null,
            'due_date' => $this->normDate($in['due_date'] ?? null),
            'reminder_minutes' => isset($in['reminder_minutes']) ? (int) $in['reminder_minutes'] : 30,
        ];
        if (!empty($in['category_uuid'])) {
            $cat = $this->cats->findByUuid($in['category_uuid'], $userId);
            if (!$cat) {
                return ['ok' => false, 'message' => 'Kategori tidak ditemukan', 'errors' => ['category_uuid' => 'Kategori tidak ditemukan']];
            }
            $data['category_id'] = (int) $cat->id;
        }
        if ($data['due_date'] === false) {
            return ['ok' => false, 'message' => 'Format due_date tidak valid (gunakan ISO 8601)', 'errors' => ['due_date' => 'Format tidak valid']];
        }
        return ['ok' => true, 'task' => $this->tasks->create($userId, $data)];
    }

    public function update(int $userId, string $uuid, array $in): array
    {
        $task = $this->tasks->findByUuid($uuid, $userId);
        if (!$task) {
            return ['ok' => false, 'status' => 404, 'message' => 'Data tidak ditemukan'];
        }
        $v = new Validator($in);
        $v->rule('lengthMax', 'title', 255);
        $v->rule('in', 'status', ['pending', 'completed']);
        if (!$v->validate()) {
            return ['ok' => false, 'message' => 'Validasi gagal', 'errors' => $this->flat($v->errors())];
        }
        $data = [];
        foreach (['title', 'description'] as $f) {
            if (array_key_exists($f, $in)) {
                $data[$f] = $in[$f];
            }
        }
        if (array_key_exists('category_uuid', $in)) {
            if (empty($in['category_uuid'])) {
                $data['category_id'] = null;
            } else {
                $cat = $this->cats->findByUuid($in['category_uuid'], $userId);
                if (!$cat) {
                    return ['ok' => false, 'message' => 'Kategori tidak ditemukan'];
                }
                $data['category_id'] = (int) $cat->id;
            }
        }
        if (array_key_exists('due_date', $in)) {
            $norm = $this->normDate($in['due_date']);
            if ($norm === false) {
                return ['ok' => false, 'message' => 'Format due_date tidak valid'];
            }
            $data['due_date'] = $norm;
            $data['is_reminded'] = false;
        }
        if (isset($in['reminder_minutes'])) {
            $data['reminder_minutes'] = (int) $in['reminder_minutes'];
            $data['is_reminded'] = false;
        }
        if (isset($in['status'])) {
            $data['status'] = $in['status'];
            $data['completed_at'] = $in['status'] === 'completed' ? date('Y-m-d H:i:s') : null;
        }
        if ($data) {
            $this->tasks->update((int) $task->id, $data);
        }
        return ['ok' => true, 'task' => $this->tasks->findByUuid($uuid, $userId)];
    }

    public function setStatus(int $userId, string $uuid, string $status): array
    {
        return $this->update($userId, $uuid, ['status' => $status]);
    }

    public function delete(int $userId, string $uuid): array
    {
        $task = $this->tasks->findByUuid($uuid, $userId);
        if (!$task) {
            return ['ok' => false, 'status' => 404, 'message' => 'Data tidak ditemukan'];
        }
        $this->tasks->delete((int) $task->id);
        return ['ok' => true];
    }

    public function reminders(int $userId): array
    {
        $rows = $this->tasks->dueReminders($userId);
        $this->tasks->markReminded(array_map(fn($t) => (int) $t->id, $rows));
        return array_map(fn($t) => $t->toArray(), $rows);
    }

    public function stats(int $userId): array
    {
        return $this->tasks->statsByUser($userId);
    }

    private function normDate(mixed $v): string|false|null
    {
        if ($v === null || $v === '') {
            return null;
        }
        $ts = strtotime((string) $v);
        return $ts === false ? false : date('Y-m-d H:i:s', $ts);
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
