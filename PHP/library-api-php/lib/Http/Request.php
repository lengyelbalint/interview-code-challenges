<?php

final class Request
{
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function path(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function queryInt(string $key, int $default = 0): int
    {
        return (int) ($this->query($key, $default));
    }

    public function json(): array
    {
        $raw = $GLOBALS['__test_raw_body'] ?? file_get_contents('php://input');

        if (isset($GLOBALS['__test_raw_body'])) {
            unset($GLOBALS['__test_raw_body']);
        }

        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}