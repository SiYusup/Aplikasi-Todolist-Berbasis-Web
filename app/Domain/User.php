<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Domain;

class User
{
    public function __construct(
        public ?int $id = null,
        public ?string $uuid = null,
        public string $username = '',
        public string $passwordHash = '',
        public string $status = 'active',
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: isset($row['id']) ? (int) $row['id'] : null,
            uuid: $row['uuid'] ?? null,
            username: $row['username'] ?? '',
            passwordHash: $row['password_hash'] ?? '',
            status: $row['status'] ?? 'active',
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null,
        );
    }

    public function toPublicArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'username' => $this->username,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
