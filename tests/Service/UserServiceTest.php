<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Tests\Service;

use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use UCrazy\AplikasiTodolist\Config\Database;
use UCrazy\AplikasiTodolist\Repository\UserRepository;
use UCrazy\AplikasiTodolist\Service\UserService;

class UserServiceTest extends TestCase
{
    private Medoo $db;
    private UserService $svc;
    private string $username;
    private string $other = '';

    protected function setUp(): void
    {
        $_ENV['DB_HOST'] = getenv('DB_HOST') ?: 'localhost';
        foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $k) {
            $_ENV[$k] = getenv($k) ?: $_ENV[$k] ?? '';
        }
        Database::setInstance(null);
        $this->db = Database::connection();
        $this->svc = new UserService(new UserRepository($this->db));
        $this->username = 'test_' . substr(bin2hex(random_bytes(4)), 0, 8);
    }

    protected function tearDown(): void
    {
        foreach ([$this->username, $this->username . '_new', $this->other] as $u) {
            if ($u !== '') {
                $this->db->delete('users', ['username' => $u]);
            }
        }
    }

    public function testRegisterSuccess(): void
    {
        $r = $this->svc->register(['username' => $this->username, 'password' => 'rahasia123']);
        $this->assertTrue($r['ok']);
        $this->assertSame($this->username, $r['user']->username);
    }

    public function testRegisterValidationFails(): void
    {
        $r = $this->svc->register(['username' => 'ab', 'password' => '123']);
        $this->assertFalse($r['ok']);
        $this->assertArrayHasKey('username', $r['errors']);
    }

    public function testLoginWrongPasswordAndLockoutShape(): void
    {
        $this->svc->register(['username' => $this->username, 'password' => 'rahasia123']);
        $r = $this->svc->login(['username' => $this->username, 'password' => 'salah']);
        $this->assertFalse($r['ok']);
        $this->assertSame(401, $r['status']);
    }

    public function testLoginSuccessReturnsSplitToken(): void
    {
        $this->svc->register(['username' => $this->username, 'password' => 'rahasia123']);
        $r = $this->svc->login(['username' => $this->username, 'password' => 'rahasia123']);
        $this->assertTrue($r['ok']);
        $this->assertStringContainsString(':', $r['token']);
    }

    public function testChangeUsernameSuccess(): void
    {
        $reg = $this->svc->register(['username' => $this->username, 'password' => 'rahasia123']);
        $r = $this->svc->changeUsername($reg['user'], [
            'new_username' => $this->username . '_new',
            'current_password' => 'rahasia123',
        ]);
        $this->assertTrue($r['ok']);
        $this->assertSame($this->username . '_new', $r['user']->username);
    }

    public function testChangeUsernameWrongPassword(): void
    {
        $reg = $this->svc->register(['username' => $this->username, 'password' => 'rahasia123']);
        $r = $this->svc->changeUsername($reg['user'], [
            'new_username' => 'nama_baru_xyz',
            'current_password' => 'salah',
        ]);
        $this->assertFalse($r['ok']);
        $this->assertSame(401, $r['status']);
    }

    public function testChangeUsernameDuplicate(): void
    {
        $this->other = 'other_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $this->svc->register(['username' => $this->other, 'password' => 'rahasia123']);
        $reg = $this->svc->register(['username' => $this->username, 'password' => 'rahasia123']);
        $r = $this->svc->changeUsername($reg['user'], [
            'new_username' => $this->other,
            'current_password' => 'rahasia123',
        ]);
        $this->assertFalse($r['ok']);
        $this->assertSame(409, $r['status']);
    }

    public function testChangePasswordSuccess(): void
    {
        $reg = $this->svc->register(['username' => $this->username, 'password' => 'rahasia123']);
        $r = $this->svc->changePassword($reg['user'], [
            'old_password' => 'rahasia123',
            'new_password' => 'baru456789',
        ]);
        $this->assertTrue($r['ok']);
        $login = $this->svc->login(['username' => $this->username, 'password' => 'baru456789']);
        $this->assertTrue($login['ok']);
    }

    public function testChangePasswordWrongOld(): void
    {
        $reg = $this->svc->register(['username' => $this->username, 'password' => 'rahasia123']);
        $r = $this->svc->changePassword($reg['user'], [
            'old_password' => 'salah',
            'new_password' => 'baru456789',
        ]);
        $this->assertFalse($r['ok']);
        $this->assertSame(401, $r['status']);
    }
}
