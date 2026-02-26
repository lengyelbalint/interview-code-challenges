<?php

require_once __DIR__ . '/../lib/Http/JsonResponse.php';
require_once __DIR__ . '/../lib/ApiException.php';
require_once __DIR__ . '/../services/BookService.php';

final class BookController
{
    public function __construct(private BookService $service)
    {
    }

    public function index(): void
    {
        try {
            JsonResponse::ok($this->service->listBooks());
        } catch (\Throwable $e) {
            JsonResponse::error('Unexpected error', 500);
        }
    }
}