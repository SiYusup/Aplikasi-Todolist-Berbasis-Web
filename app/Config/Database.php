<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Config;

use Medoo\Medoo;
use Dotenv\Dotenv;

class Database
{
    private static ?Medoo $instance = null;

    public static function env(string $key, string $default = ''): string
    {
        $val = $_ENV[$key] ?? getenv($key);
        return $val === false || $val === null ? $default : (string) $val;
    }

    public static function connection(): Medoo
    {
        if (self::$instance === null) {
            $root = dirname(__DIR__, 2);
            if (file_exists($root . '/.env')) {
                Dotenv::createImmutable($root)->safeLoad();
            }
            self::$instance = new Medoo([
                'type' => 'pgsql',
                'host' => self::env('DB_HOST', 'localhost'),
                'port' => (int) self::env('DB_PORT', '5432'),
                'database' => self::env('DB_NAME', 'todolist'),
                'username' => self::env('DB_USER', 'postgres'),
                'password' => self::env('DB_PASS', 'ucup'),
            ]);
        }
        return self::$instance;
    }

    /** For tests: inject/override connection. */
    public static function setInstance(?Medoo $db): void
    {
        self::$instance = $db;
    }
}
