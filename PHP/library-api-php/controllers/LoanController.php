<?php

require_once __DIR__ . '/../lib/Http/JsonResponse.php';
require_once __DIR__ . '/../lib/Http/Request.php';
require_once __DIR__ . '/../services/LoanService.php';
require_once __DIR__ . '/../lib/ApiException.php';

final class LoanController
{
    public function __construct(
        private LoanService $service,
        private Request $request
    ) {
    }

    public function index(): void
    {
        try {
            JsonResponse::ok($this->service->listActiveLoans());
        } catch (\Throwable $e) {
            JsonResponse::error('Unexpected error', 500);
        }
    }

    public function returnBook(): void
    {
        try {
            $payload = $this->request->json();
            $bookStockId = (int) ($payload['bookStockId'] ?? 0);
            $returnedAt = (string) ($payload['returnedAt'] ?? date('Y-m-d'));

            $result = $this->service->returnBook($bookStockId, $returnedAt);
            JsonResponse::ok($result);
        } catch (ApiException $e) {
            JsonResponse::error($e->getMessage(), $e->status, $e->errorCode, $e->meta);
        } catch (\Throwable $e) {
            JsonResponse::error('Unexpected error', 500);
        }
    }
}