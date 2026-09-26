<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Middleware;

use UCrazy\AplikasiTodolist\Config\Database;
use UCrazy\AplikasiTodolist\Domain\User;
use UCrazy\AplikasiTodolist\Helper\Response;
use UCrazy\AplikasiTodolist\Service\UserService;

class AuthMiddleware
{
    private static ?User $cached = null;

    public static function cookieName(): string
    {
        return Database::env('SESSION_COOKIE', 'todolist_session');
    }

    public static function user(): ?User
    {
        if (self::$cached !== null) {
            return self::$cached;
        }
        $cookie = $_COOKIE[self::cookieName()] ?? null;
        if (!$cookie) {
            return null;
        }
        self::$cached = UserService::make()->resolveSessionUser($cookie);
        return self::$cached;
    }

    /** For JSON API: 401 if guest. Returns user. */
    public static function requireApi(): ?User
    {
        $user = self::user();
        if (!$user) {
            Response::fail('Unauthorized', 401);
            return null;
        }
        return $user;
    }

    public static function setLoginCookie(string $token, int $days): void
    {
        $name = self::cookieName();
        setcookie($name, $token, [
            'expires' => time() + $days * 86400,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[$name] = $token;
    }

    public static function clearCookie(): void
    {
        $name = self::cookieName();
        setcookie($name, '', ['expires' => time() - 3600, 'path' => '/']);
        unset($_COOKIE[$name]);
    }
}
