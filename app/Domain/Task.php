<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Domain;

class Task
{
    public function __construct(
        public ?int $id = null,
        public ?string $uuid = null,
        public ?int $userId = null,
        public ?int $categoryId = null,
        public ?string $categoryUuid = null,
        public ?string $categoryName = null,
        public ?string $categoryColor = null,
        public string $title = '',
        public ?string $description = null,
        public string $status = 'pending',
        public ?string $dueDate = null,
        public int $reminderMinutes = 30,
        public bool $isReminded = false,
        public ?string $completedAt = null,
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
            categoryId: isset($row['category_id']) ? (int) $row['category_id'] : null,
            categoryUuid: $row['category_uuid'] ?? null,
            categoryName: $row['category_name'] ?? null,
            categoryColor: $row['category_color'] ?? null,
            title: $row['title'] ?? '',
            description: $row['description'] ?? null,
            status: $row['status'] ?? 'pending',
            dueDate: $row['due_date'] ?? null,
            reminderMinutes: isset($row['reminder_minutes']) ? (int) $row['reminder_minutes'] : 30,
            isReminded: (bool) ($row['is_reminded'] ?? false),
            completedAt: $row['completed_at'] ?? null,
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'category' => $this->categoryUuid ? [
                'uuid' => $this->categoryUuid,
                'name' => $this->categoryName,
                'color' => $this->categoryColor,
            ] : null,
            'due_date' => $this->dueDate,
            'reminder_minutes' => $this->reminderMinutes,
            'is_reminded' => $this->isReminded,
            'completed_at' => $this->completedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
