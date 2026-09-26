<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Repository;

use Medoo\Medoo;
use Ramsey\Uuid\Uuid;
use UCrazy\AplikasiTodolist\Domain\Task;

class TaskRepository
{
    public function __construct(private Medoo $db)
    {
    }

    private function mapRows(array $rows): array
    {
        return array_map(fn($r) => Task::fromRow($r), $rows);
    }

    private function baseJoin(): array
    {
        return ['[>]categories' => ['category_id' => 'id']];
    }

    private function columns(): array
    {
        return [
            'tasks.id', 'tasks.uuid', 'tasks.user_id', 'tasks.category_id',
            'tasks.title', 'tasks.description', 'tasks.status',
            'tasks.due_date', 'tasks.reminder_minutes', 'tasks.is_reminded',
            'tasks.completed_at', 'tasks.created_at', 'tasks.updated_at',
            'categories.uuid(category_uuid)', 'categories.name(category_name)', 'categories.color(category_color)',
        ];
    }

    /** @return Task[] */
    public function allByUser(int $userId, ?string $status = null, ?int $categoryId = null, int $limit = 50, int $offset = 0): array
    {
        $where = ['tasks.user_id' => $userId, 'ORDER' => ['tasks.created_at' => 'DESC'], 'LIMIT' => [$offset, $limit]];
        if ($status) {
            $where['tasks.status'] = $status;
        }
        if ($categoryId) {
            $where['tasks.category_id'] = $categoryId;
        }
        $rows = $this->db->select('tasks', $this->baseJoin(), $this->columns(), $where);
        return $this->mapRows($rows ?: []);
    }

    public function countByUser(int $userId, ?string $status = null): int
    {
        $where = ['user_id' => $userId];
        if ($status) {
            $where['status'] = $status;
        }
        return (int) $this->db->count('tasks', $where);
    }

    public function findByUuid(string $uuid, int $userId): ?Task
    {
        $rows = $this->db->select('tasks', $this->baseJoin(), $this->columns(), [
            'tasks.uuid' => $uuid, 'tasks.user_id' => $userId, 'LIMIT' => 1,
        ]);
        return $rows ? Task::fromRow($rows[0]) : null;
    }

    public function create(int $userId, array $data): Task
    {
        $uuid = Uuid::uuid4()->toString();
        $this->db->insert('tasks', [
            'uuid' => $uuid,
            'user_id' => $userId,
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'reminder_minutes' => $data['reminder_minutes'] ?? 30,
        ]);
        return $this->findByUuid($uuid, $userId);
    }

    public function update(int $id, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update('tasks', $data, ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->db->delete('tasks', ['id' => $id]);
    }

    /** @return Task[] tugas pending yang masuk jendela pengingat & belum diingatkan */
    public function dueReminders(int $userId): array
    {
        $sql = 'SELECT t.id, t.uuid, t.user_id, t.category_id, t.title, t.description, t.status,
                t.due_date, t.reminder_minutes, t.is_reminded, t.completed_at, t.created_at, t.updated_at,
                c.uuid AS category_uuid, c.name AS category_name, c.color AS category_color
            FROM tasks t LEFT JOIN categories c ON c.id = t.category_id
            WHERE t.user_id = :uid AND t.status = \'pending\' AND t.is_reminded = FALSE
              AND t.due_date IS NOT NULL
              AND t.due_date <= NOW() + (t.reminder_minutes || \' minutes\')::interval
            ORDER BY t.due_date ASC LIMIT 20';
        $stmt = $this->db->pdo->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $this->mapRows($stmt->fetchAll(\PDO::FETCH_ASSOC) ?: []);
    }

    public function markReminded(array $ids): void
    {
        if (!$ids) {
            return;
        }
        $this->db->update('tasks', ['is_reminded' => true], ['id' => $ids]);
    }

    public function statsByUser(int $userId): array
    {
        $total = (int) $this->db->count('tasks', ['user_id' => $userId]);
        $pending = (int) $this->db->count('tasks', ['user_id' => $userId, 'status' => 'pending']);
        $completed = (int) $this->db->count('tasks', ['user_id' => $userId, 'status' => 'completed']);
        $rows = $this->db->query(
            'SELECT c.uuid AS category_uuid, c.name AS category_name, c.color AS category_color,
                COUNT(t.id)::int AS total,
                COUNT(t.id) FILTER (WHERE t.status = \'pending\')::int AS pending,
                COUNT(t.id) FILTER (WHERE t.status = \'completed\')::int AS completed
             FROM categories c LEFT JOIN tasks t ON t.category_id = c.id AND t.user_id = c.user_id
             WHERE c.user_id = :uid GROUP BY c.uuid, c.name, c.color ORDER BY c.name',
            ['uid' => $userId]
        )->fetchAll(\PDO::FETCH_ASSOC);
        return ['total' => $total, 'pending' => $pending, 'completed' => $completed, 'by_category' => $rows ?: []];
    }
}
