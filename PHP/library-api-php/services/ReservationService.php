<?php

require_once __DIR__ . '/../lib/ApiException.php';
require_once __DIR__ . '/../repositories/BookRepository.php';
require_once __DIR__ . '/../repositories/BorrowerRepository.php';
require_once __DIR__ . '/../repositories/BookStockRepository.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../models/Reservation.php';

final class ReservationService
{
    private const ASSUMED_LOAN_DAYS = 14;

    public function __construct(
        private BookRepository $books,
        private BorrowerRepository $borrowers,
        private BookStockRepository $bookStocks,
        private ReservationRepository $reservations
    ) {
    }

    public function reserve(int $bookId, int $borrowerId): array
    {
        $this->assertValidReserveInput($bookId, $borrowerId);
        $this->assertBookExists($bookId);
        $this->assertBorrowerExists($borrowerId);
        $this->assertReservableNow($bookId);
        $this->assertNoDuplicateActiveReservation($bookId, $borrowerId);

        $id = $this->reservations->nextId();
        $reservation = new Reservation($id, $bookId, $borrowerId, date('c'), 'active');
        $this->reservations->add($reservation);

        $queue = $this->reservations->activeQueueForBook($bookId);
        $position = $this->positionInQueue($queue, $borrowerId);

        return [
            'id' => $reservation->id,
            'bookId' => $reservation->bookId,
            'borrowerId' => $reservation->borrowerId,
            'reservedAt' => $reservation->reservedAt,
            'status' => $reservation->status,
            'position' => $position,
            'queueLength' => count($queue),
        ];
    }

    public function status(int $bookId, int $borrowerId): array
    {
        $this->assertValidStatusInput($bookId, $borrowerId);
        $this->assertBookExists($bookId);

        $queue = $this->reservations->activeQueueForBook($bookId);
        $position = $this->getQueuePositionOrFail($queue, $borrowerId);

        $availableNow = $this->bookStocks->hasAvailableCopy($bookId);
        $earliestLoanEndDate = $availableNow ? null : $this->bookStocks->getEarliestLoanEndDate($bookId);

        $estimatedAvailableFrom = null;
        $baseDate = $availableNow ? date('Y-m-d') : $earliestLoanEndDate;

        if ($baseDate !== null) {
            $offsetDays = ($position - 1) * self::ASSUMED_LOAN_DAYS;
            $estimatedAvailableFrom = date('Y-m-d', strtotime($baseDate . ' +' . $offsetDays . ' days'));
        }

        return [
            'bookId' => $bookId,
            'borrowerId' => $borrowerId,
            'position' => $position,
            'queueLength' => count($queue),
            'availableNow' => $availableNow,
            'earliestLoanEndDate' => $earliestLoanEndDate,
            'estimatedAvailableFrom' => $estimatedAvailableFrom,
            'assumptions' => [
                'assumedLoanDaysPerReservation' => self::ASSUMED_LOAN_DAYS,
            ],
        ];
    }

    /** @param Reservation[] $queue */
    private function positionInQueue(array $queue, int $borrowerId): ?int
    {
        foreach ($queue as $i => $r) {
            if ((int) $r->borrowerId === $borrowerId) {
                return $i + 1;
            }
        }
        return null;
    }

    private function assertValidStatusInput(int $bookId, int $borrowerId): void
    {
        if ($bookId <= 0 || $borrowerId <= 0) {
            throw new ApiException(422, 'bookId and borrowerId are required as query params', 'VALIDATION_ERROR');
        }
    }

    private function getQueuePositionOrFail(array $queue, int $borrowerId): int
    {
        $position = $this->positionInQueue($queue, $borrowerId);

        if ($position === null) {
            throw new ApiException(404, 'Active reservation not found for this borrower and book', 'NOT_FOUND');
        }

        return $position;
    }

    private function assertValidReserveInput(int $bookId, int $borrowerId): void
    {
        if ($bookId <= 0 || $borrowerId <= 0) {
            throw new ApiException(422, 'bookId and borrowerId are required', 'VALIDATION_ERROR');
        }
    }

    private function assertBookExists(int $bookId): void
    {
        if ($this->books->find($bookId) === null) {
            throw new ApiException(404, 'Book not found', 'NOT_FOUND');
        }
    }

    private function assertBorrowerExists(int $borrowerId): void
    {
        if (!$this->borrowers->exists($borrowerId)) {
            throw new ApiException(404, 'Borrower not found', 'NOT_FOUND');
        }
    }

    private function assertReservableNow(int $bookId): void
    {
        if (!$this->bookStocks->isBookOnLoan($bookId)) {
            throw new ApiException(409, 'Book is not currently on loan; reservation is not allowed', 'CONFLICT');
        }
    }

    private function assertNoDuplicateActiveReservation(int $bookId, int $borrowerId): void
    {
        if ($this->reservations->hasActiveReservation($bookId, $borrowerId)) {
            throw new ApiException(409, 'Active reservation already exists for this borrower and book', 'CONFLICT');
        }
    }
}