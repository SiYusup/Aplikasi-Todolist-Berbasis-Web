<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Domain;

class Category
{
    public function __construct(
        public ?int $id = null,
        public ?string $uuid = null,
        public ?int $userId = null,
        public string $name = '',
        public string $color = 'zinc',
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: isset($row['id']) ? (int) $row['id'] : null,
            uuid: $row['uuid'] ?? null,
            userId: isset($row['user_id']) ? (int) $row['user_id'] : null,
            name: $row['name'] ?? '',
            color: $row['color'] ?? 'zinc',
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'color' => $this->color,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
