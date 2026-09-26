<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Tests\Service;

use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use UCrazy\AplikasiTodolist\Config\Database;
use UCrazy\AplikasiTodolist\Repository\CategoryRepository;
use UCrazy\AplikasiTodolist\Repository\TaskRepository;
use UCrazy\AplikasiTodolist\Repository\UserRepository;
use UCrazy\AplikasiTodolist\Service\TaskService;
use UCrazy\AplikasiTodolist\Service\UserService;

class TaskServiceTest extends TestCase
{
    private Medoo $db;
    private TaskService $svc;
    private int $userId;
    private string $username;

    protected function setUp(): void
    {
        Database::setInstance(null);
        $this->db = Database::connection();
        $this->username = 'task_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $userSvc = new UserService(new UserRepository($this->db));
        $r = $userSvc->register(['username' => $this->username, 'password' => 'rahasia123']);
        $this->assertTrue($r['ok']);
        $this->userId = (int) $r['user']->id;
        $this->svc = new TaskService(new TaskRepository($this->db), new CategoryRepository($this->db));
    }

    protected function tearDown(): void
    {
        $this->db->delete('tasks', ['user_id' => $this->userId]);
        $this->db->delete('categories', ['user_id' => $this->userId]);
        $this->db->delete('user_sessions', ['user_id' => $this->userId]);
        $this->db->delete('audit_logs', ['user_id' => $this->userId]);
        $this->db->delete('user_security', ['user_id' => $this->userId]);
        $this->db->delete('users', ['id' => $this->userId]);
    }

    public function testCreateAndComplete(): void
    {
        $c = $this->svc->create($this->userId, ['title' => 'Belajar PHP MVC']);
        $this->assertTrue($c['ok']);
        $uuid = $c['task']->uuid;

        $done = $this->svc->setStatus($this->userId, $uuid, 'completed');
        $this->assertTrue($done['ok']);
        $this->assertSame('completed', $done['task']->status);

        $stats = $this->svc->stats($this->userId);
        $this->assertSame(1, $stats['total']);
        $this->assertSame(1, $stats['completed']);
    }

    public function testCreateValidationFailsWithoutTitle(): void
    {
        $r = $this->svc->create($this->userId, ['title' => '']);
        $this->assertFalse($r['ok']);
    }

    public function testRemindersEmptyWhenNoDueDate(): void
    {
        $this->svc->create($this->userId, ['title' => 'Tanpa due date']);
        $this->assertSame([], $this->svc->reminders($this->userId));
    }

    public function testUpdateInvalidStatusFails(): void
    {
        $c = $this->svc->create($this->userId, ['title' => 'Status aneh']);
        $r = $this->svc->update($this->userId, $c['task']->uuid, ['status' => 'bogus']);
        $this->assertFalse($r['ok']);
    }

    public function testDeleteMissingReturns404(): void
    {
        $r = $this->svc->delete($this->userId, '00000000-0000-4000-8000-000000000000');
        $this->assertFalse($r['ok']);
        $this->assertSame(404, $r['status']);
    }

    public function testStatsIncludesCategoryColor(): void
    {
        $catRepo = new CategoryRepository($this->db);
        $cat = $catRepo->create($this->userId, 'Kerja', '#7c3aed');
        $this->svc->create($this->userId, ['title' => 'Tugas ungu', 'category_uuid' => $cat->uuid]);
        $stats = $this->svc->stats($this->userId);
        $this->assertSame(1, $stats['total']);
        $this->assertSame('Kerja', $stats['by_category'][0]['category_name']);
        $this->assertSame('#7c3aed', $stats['by_category'][0]['category_color']);
    }

    public function testReopenFlow(): void
    {
        $c = $this->svc->create($this->userId, ['title' => 'Buka tutup']);
        $uuid = $c['task']->uuid;
        $this->svc->setStatus($this->userId, $uuid, 'completed');
        $back = $this->svc->setStatus($this->userId, $uuid, 'pending');
        $this->assertTrue($back['ok']);
        $this->assertSame('pending', $back['task']->status);
    }
}
