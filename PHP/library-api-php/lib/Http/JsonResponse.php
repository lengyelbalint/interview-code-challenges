<?php

final class JsonResponse
{
    public static function send(array $payload, int $status = 200): void
    {
        http_response_code($status);
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode($payload);
    }

    public static function ok(array $data, int $status = 200): void
    {
        self::send(['data' => $data], $status);
    }

    public static function error(string $message, int $status, ?string $code = null, array $meta = []): void
    {
        self::send(['error' => ['message' => $message, 'code' => $code, 'meta' => $meta]], $status);
    }
}