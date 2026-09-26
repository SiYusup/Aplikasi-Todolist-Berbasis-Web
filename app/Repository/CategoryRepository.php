<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Repository;

use Medoo\Medoo;
use Ramsey\Uuid\Uuid;
use UCrazy\AplikasiTodolist\Domain\Category;

class CategoryRepository
{
    public function __construct(private Medoo $db)
    {
    }

    /** @return Category[] */
    public function allByUser(int $userId): array
    {
        $rows = $this->db->select('categories', '*', ['user_id' => $userId, 'ORDER' => ['name' => 'ASC']]);
        return array_map(fn($r) => Category::fromRow($r), $rows ?: []);
    }

    public function findByUuid(string $uuid, int $userId): ?Category
    {
        $row = $this->db->get('categories', '*', ['uuid' => $uuid, 'user_id' => $userId]);
        return $row ? Category::fromRow($row) : null;
    }

    public function create(int $userId, string $name, string $color = 'zinc'): Category
    {
        $uuid = Uuid::uuid4()->toString();
        $this->db->insert('categories', [
            'uuid' => $uuid,
            'user_id' => $userId,
            'name' => $name,
            'color' => $color,
        ]);
        return $this->findByUuid($uuid, $userId);
    }

    public function update(int $id, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update('categories', $data, ['id' => $id]);
    }

    public function delete(int $id): void
    {
        // tasks.category_id otomatis NULL via FK ON DELETE SET NULL
        $this->db->delete('categories', ['id' => $id]);
    }
}
