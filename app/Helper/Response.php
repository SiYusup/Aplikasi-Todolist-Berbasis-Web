<?php

declare(strict_types=1);

namespace UCrazy\AplikasiTodolist\Helper;

class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public static function ok(mixed $data = null, string $message = 'OK'): void
    {
        self::json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    public static function data(mixed $data): void
    {
        self::json(['success' => true, 'data' => $data]);
    }

    public static function fail(string $message, int $status = 400, ?array $errors = null, mixed $extra = null): void
    {
        $body = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        if ($extra !== null) {
            $body['data'] = $extra;
        }
        self::json($body, $status);
    }

    public static function input(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        if ($raw === '') {
            return $_POST;
        }
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        parse_str($raw, $form);
        return is_array($form) ? $form : [];
    }

    public static function clientIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
