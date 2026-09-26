<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Service;

use UCrazy\AplikasiTodolist\Config\Database;
use UCrazy\AplikasiTodolist\Domain\User;
use UCrazy\AplikasiTodolist\Helper\Response;
use UCrazy\AplikasiTodolist\Repository\UserRepository;
use Valitron\Validator;

class UserService
{
    public function __construct(private UserRepository $users)
    {
    }

    public static function make(): self
    {
        return new self(new UserRepository(Database::connection()));
    }

    private function fail(string $msg, ?array $errors = null): array
    {
        return ['ok' => false, 'message' => $msg, 'errors' => $errors];
    }

    public function register(array $in): array
    {
        $v = new Validator($in);
        $v->rule('required', ['username', 'password']);
        $v->rule('lengthBetween', 'username', 3, 50);
        $v->rule('lengthMin', 'password', 6);
        if (!$v->validate()) {
            return $this->fail('Validasi gagal', $this->flatErrors($v->errors()));
        }
        $username = trim($in['username']);
        if ($this->users->findByUsername($username)) {
            return $this->fail('Username sudah dipakai', ['username' => 'Username sudah dipakai']);
        }
        $user = $this->users->create($username, password_hash($in['password'], PASSWORD_DEFAULT));
        $this->users->audit($user->id, 'register', Response::clientIp());
        return ['ok' => true, 'user' => $user];
    }

    public function login(array $in): array
    {
        $v = new Validator($in);
        $v->rule('required', ['username', 'password']);
        if (!$v->validate()) {
            return $this->fail('Validasi gagal', $this->flatErrors($v->errors()));
        }
        $ip = Response::clientIp();
        $user = $this->users->findByUsername(trim($in['username']));

        if (!$user) {
            return ['ok' => false, 'status' => 401, 'message' => 'Username atau password salah'];
        }
        $sec = $this->users->getSecurity((int) $user->id);
        if (!empty($sec['lockout_until']) && strtotime((string) $sec['lockout_until']) > time()) {
            return ['ok' => false, 'status' => 423, 'message' => 'Akun terkunci, coba lagi nanti',
                'data' => ['lockout_until' => $sec['lockout_until']]];
        }
        if (!password_verify($in['password'], $user->passwordHash)) {
            $lockout = $this->users->recordFailedLogin((int) $user->id, $ip);
            $this->users->audit($user->id, 'login_failed', $ip);
            if ($lockout) {
                return ['ok' => false, 'status' => 423, 'message' => 'Akun terkunci, coba lagi dalam 5 menit',
                    'data' => ['lockout_until' => $lockout]];
            }
            return ['ok' => false, 'status' => 401, 'message' => 'Username atau password salah'];
        }

        $this->users->resetFailedLogin((int) $user->id, $ip);
        $this->users->audit($user->id, 'login', $ip);
        $remember = !empty($in['remember_me']);
        $days = (int) Database::env($remember ? 'REMEMBER_DAYS' : 'SESSION_DAYS', $remember ? '30' : '7');
        $selector = bin2hex(random_bytes(6)); // 12 char
        $validator = bin2hex(random_bytes(32));
        $this->users->createSession(
            (int) $user->id, $selector, hash('sha256', $validator),
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            date('Y-m-d H:i:s', time() + $days * 86400)
        );
        return ['ok' => true, 'user' => $user, 'token' => $selector . ':' . $validator, 'days' => $days];
    }

    public function resolveSessionUser(?string $cookie): ?User
    {
        if (!$cookie || !str_contains($cookie, ':')) {
            return null;
        }
        [$selector, $validator] = explode(':', $cookie, 2);
        $sess = $this->users->findSession($selector);
        if (!$sess || strtotime($sess['expires_at']) < time()) {
            return null;
        }
        if (!hash_equals($sess['hashed_validator'], hash('sha256', $validator))) {
            return null;
        }
        return $this->users->findById((int) $sess['user_id']);
    }

    public function logout(?string $cookie): void
    {
        if ($cookie && str_contains($cookie, ':')) {
            $this->users->deleteSession(explode(':', $cookie, 2)[0]);
        }
    }

    public function changeUsername(User $user, array $in): array
    {
        $v = new Validator($in);
        $v->rule('required', ['new_username', 'current_password']);
        $v->rule('lengthBetween', 'new_username', 3, 50);
        if (!$v->validate()) {
            return $this->fail('Validasi gagal', $this->flatErrors($v->errors()));
        }
        if (!password_verify($in['current_password'], $user->passwordHash)) {
            return ['ok' => false, 'status' => 401, 'message' => 'Password lama salah'];
        }
        $new = trim($in['new_username']);
        $exists = $this->users->findByUsername($new);
        if ($exists && $exists->id !== $user->id) {
            return ['ok' => false, 'status' => 409, 'message' => 'Username sudah dipakai'];
        }
        $this->users->updateUsername((int) $user->id, $new);
        $this->users->audit($user->id, 'change_username', Response::clientIp());
        return ['ok' => true, 'user' => $this->users->findById((int) $user->id)];
    }

    public function changePassword(User $user, array $in): array
    {
        $v = new Validator($in);
        $v->rule('required', ['old_password', 'new_password']);
        $v->rule('lengthMin', 'new_password', 6);
        if (!$v->validate()) {
            return $this->fail('Validasi gagal', $this->flatErrors($v->errors()));
        }
        if (!password_verify($in['old_password'], $user->passwordHash)) {
            return ['ok' => false, 'status' => 401, 'message' => 'Password lama salah'];
        }
        $this->users->updatePassword((int) $user->id, password_hash($in['new_password'], PASSWORD_DEFAULT));
        $this->users->audit($user->id, 'change_password', Response::clientIp());
        return ['ok' => true];
    }

    private function flatErrors(array $errors): array
    {
        $out = [];
        foreach ($errors as $field => $msgs) {
            $out[$field] = implode(', ', (array) $msgs);
        }
        return $out;
    }
}
