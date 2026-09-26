<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Tests\Service;

use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use UCrazy\AplikasiTodolist\Config\Database;
use UCrazy\AplikasiTodolist\Repository\CategoryRepository;
use UCrazy\AplikasiTodolist\Repository\UserRepository;
use UCrazy\AplikasiTodolist\Service\CategoryService;
use UCrazy\AplikasiTodolist\Service\UserService;

class CategoryServiceTest extends TestCase
{
    private Medoo $db;
    private CategoryService $svc;
    private int $userId;
    private string $username;

    protected function setUp(): void
    {
        Database::setInstance(null);
        $this->db = Database::connection();
        $this->username = 'cat_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $userSvc = new UserService(new UserRepository($this->db));
        $r = $userSvc->register(['username' => $this->username, 'password' => 'rahasia123']);
        $this->assertTrue($r['ok']);
        $this->userId = (int) $r['user']->id;
        $this->svc = new CategoryService(new CategoryRepository($this->db));
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

    public function testCreateWithHexColor(): void
    {
        $r = $this->svc->create($this->userId, ['name' => 'Kerja', 'color' => '#7c3aed']);
        $this->assertTrue($r['ok']);
        $this->assertSame('#7c3aed', $r['category']->color);
    }

    public function testCreateValidationFailsWithoutName(): void
    {
        $r = $this->svc->create($this->userId, ['name' => '']);
        $this->assertFalse($r['ok']);
        $this->assertArrayHasKey('name', $r['errors']);
    }

    public function testListContainsCreated(): void
    {
        $this->svc->create($this->userId, ['name' => 'Kuliah', 'color' => '#1d4ed8']);
        $names = array_column($this->svc->list($this->userId), 'name');
        $this->assertContains('Kuliah', $names);
    }

    public function testUpdateColor(): void
    {
        $c = $this->svc->create($this->userId, ['name' => 'Hobi', 'color' => '#71717a']);
        $u = $this->svc->update($this->userId, $c['category']->uuid, ['color' => '#15803d']);
        $this->assertTrue($u['ok']);
        $this->assertSame('#15803d', $u['category']->color);
    }

    public function testUpdateNotFound(): void
    {
        $r = $this->svc->update($this->userId, '00000000-0000-4000-8000-000000000000', ['name' => 'X']);
        $this->assertFalse($r['ok']);
        $this->assertSame(404, $r['status']);
    }

    public function testDelete(): void
    {
        $c = $this->svc->create($this->userId, ['name' => 'HapusSaya', 'color' => '#b91c1c']);
        $d = $this->svc->delete($this->userId, $c['category']->uuid);
        $this->assertTrue($d['ok']);
        $names = array_column($this->svc->list($this->userId), 'name');
        $this->assertNotContains('HapusSaya', $names);
    }

    public function testDeleteNotFound(): void
    {
        $r = $this->svc->delete($this->userId, '00000000-0000-4000-8000-000000000000');
        $this->assertFalse($r['ok']);
        $this->assertSame(404, $r['status']);
    }
}
