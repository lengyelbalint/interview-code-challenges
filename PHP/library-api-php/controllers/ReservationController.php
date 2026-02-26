<?php

require_once __DIR__ . '/../lib/Http/JsonResponse.php';
require_once __DIR__ . '/../lib/Http/Request.php';
require_once __DIR__ . '/../lib/ApiException.php';
require_once __DIR__ . '/../services/ReservationService.php';

final class ReservationController
{
    public function __construct(
        private ReservationService $service,
        private Request $request
    ) {
    }

    public function reserve(): void
    {
        try {
            $payload = $this->request->json();
            $bookId = (int) ($payload['bookId'] ?? 0);
            $borrowerId = (int) ($payload['borrowerId'] ?? 0);

            $reservation = $this->service->reserve($bookId, $borrowerId);
            JsonResponse::ok(['message' => 'Reservation created', 'reservation' => $reservation], 201);
        } catch (ApiException $e) {
            JsonResponse::error($e->getMessage(), $e->status, $e->errorCode, $e->meta);
        } catch (\Throwable $e) {
            JsonResponse::error('Unexpected error', 500);
        }
    }

    public function status(): void
    {
        try {
            $bookId = $this->request->queryInt('bookId', 0);
            $borrowerId = $this->request->queryInt('borrowerId', 0);

            $data = $this->service->status($bookId, $borrowerId);
            JsonResponse::ok($data);
        } catch (ApiException $e) {
            JsonResponse::error($e->getMessage(), $e->status, $e->errorCode, $e->meta);
        } catch (\Throwable $e) {
            JsonResponse::error('Unexpected error', 500);
        }
    }
}