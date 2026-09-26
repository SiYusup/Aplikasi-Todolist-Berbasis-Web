<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Repository;

use Medoo\Medoo;
use Ramsey\Uuid\Uuid;
use UCrazy\AplikasiTodolist\Domain\User;

class UserRepository
{
    public function __construct(private Medoo $db)
    {
    }

    public function findByUsername(string $username): ?User
    {
        $row = $this->db->get('users', '*', ['username' => $username]);
        return $row ? User::fromRow($row) : null;
    }

    public function findByUuid(string $uuid): ?User
    {
        $row = $this->db->get('users', '*', ['uuid' => $uuid]);
        return $row ? User::fromRow($row) : null;
    }

    public function findById(int $id): ?User
    {
        $row = $this->db->get('users', '*', ['id' => $id]);
        return $row ? User::fromRow($row) : null;
    }

    public function create(string $username, string $passwordHash): User
    {
        $uuid = Uuid::uuid4()->toString();
        $this->db->insert('users', [
            'uuid' => $uuid,
            'username' => $username,
            'password_hash' => $passwordHash,
        ]);
        $id = (int) $this->db->id();
        $this->db->insert('user_security', ['user_id' => $id]);
        return $this->findById($id);
    }

    public function updateUsername(int $id, string $username): void
    {
        $this->db->update('users', [
            'username' => $username,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->db->update('users', [
            'password_hash' => $hash,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);
    }

    // ---- user_security ----
    public function getSecurity(int $userId): array
    {
        $row = $this->db->get('user_security', '*', ['user_id' => $userId]);
        return $row ?: ['user_id' => $userId, 'failed_attempts' => 0, 'lockout_until' => null];
    }

    public function recordFailedLogin(int $userId, string $ip, int $maxAttempts = 5, int $lockMinutes = 5): ?string
    {
        $sec = $this->getSecurity($userId);
        $attempts = ((int) ($sec['failed_attempts'] ?? 0)) + 1;
        $data = ['failed_attempts' => $attempts, 'last_login_ip' => $ip];
        $lockoutUntil = null;
        if ($attempts >= $maxAttempts) {
            $lockoutUntil = date('Y-m-d H:i:s', time() + $lockMinutes * 60);
            $data['lockout_until'] = $lockoutUntil;
            $data['failed_attempts'] = 0;
        }
        if ($this->db->has('user_security', ['user_id' => $userId])) {
            $this->db->update('user_security', $data, ['user_id' => $userId]);
        } else {
            $this->db->insert('user_security', array_merge(['user_id' => $userId], $data));
        }
        return $lockoutUntil;
    }

    public function resetFailedLogin(int $userId, string $ip): void
    {
        $this->db->update('user_security', [
            'failed_attempts' => 0,
            'lockout_until' => null,
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip,
        ], ['user_id' => $userId]);
    }

    // ---- user_sessions (split-token) ----
    public function createSession(int $userId, string $selector, string $hashedValidator, ?string $userAgent, string $expiresAt): void
    {
        $this->db->insert('user_sessions', [
            'user_id' => $userId,
            'selector' => $selector,
            'hashed_validator' => $hashedValidator,
            'user_agent' => $userAgent,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findSession(string $selector): ?array
    {
        $row = $this->db->get('user_sessions', '*', ['selector' => $selector]);
        return $row ?: null;
    }

    public function deleteSession(string $selector): void
    {
        $this->db->delete('user_sessions', ['selector' => $selector]);
    }

    public function deleteExpiredSessions(): void
    {
        $this->db->delete('user_sessions', ['expires_at[<]' => date('Y-m-d H:i:s')]);
    }

    public function audit(?int $userId, string $event, string $ip): void
    {
        $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'event' => $event,
            'ip_address' => $ip,
        ]);
    }
}
