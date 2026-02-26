<?php

require_once __DIR__ . '/../lib/ApiException.php';
require_once __DIR__ . '/../repositories/BookRepository.php';
require_once __DIR__ . '/../repositories/BorrowerRepository.php';
require_once __DIR__ . '/../repositories/BookStockRepository.php';
require_once __DIR__ . '/../repositories/FineRepository.php';
require_once __DIR__ . '/FineCalculator.php';
require_once __DIR__ . '/../models/Fine.php';

final class LoanService
{
    public function __construct(
        private BookStockRepository $bookStocks,
        private BorrowerRepository $borrowers,
        private BookRepository $books,
        private FineRepository $fines,
        private FineCalculator $fineCalculator
    ) {
    }

    public function listActiveLoans(): array
    {
        $borrowerById = $this->borrowers->indexById();
        $bookById = $this->books->indexById();

        $resultByBorrowerId = [];

        foreach ($this->bookStocks->all() as $stock) {
            if (!$stock->isOnLoan || $stock->borrowerId === null) {
                continue;
            }

            $borrower = $borrowerById[(int) $stock->borrowerId] ?? null;
            if (!$borrower) {
                continue;
            }

            if (!isset($resultByBorrowerId[$borrower->id])) {
                $resultByBorrowerId[$borrower->id] = [
                    'borrower' => [
                        'id' => $borrower->id,
                        'name' => $borrower->name,
                        'email' => $borrower->email,
                    ],
                    'loans' => [],
                ];
            }

            $book = $bookById[(int) $stock->bookId] ?? null;

            $resultByBorrowerId[$borrower->id]['loans'][] = [
                'bookStockId' => $stock->id,
                'bookId' => $stock->bookId,
                'title' => $book?->title,
                'loanEndDate' => $stock->loanEndDate,
            ];
        }

        return array_values($resultByBorrowerId);
    }

    public function returnBook(int $bookStockId, string $returnedAt): array
    {
        $this->assertValidReturnInput($bookStockId, $returnedAt);

        $stock = $this->getOnLoanStockOrFail($bookStockId);

        $overdueDays = $this->overdueDays($stock->loanEndDate, $returnedAt);
        $fineCreated = null;

        if ($overdueDays > 0) {
            $amount = $this->fineCalculator->calculateAmount($overdueDays);
            $fineId = $this->fines->nextId();

            $bookTitle = $this->books->find((int) $stock->bookId)?->title ?? '';

            $fine = new Fine(
                $fineId,
                (int) $stock->borrowerId,
                $amount,
                sprintf(
                    'Returned late by %d day(s). bookStockId=%d, bookId=%d, title=%s, loanEndDate=%s, returnedAt=%s',
                    $overdueDays,
                    (int) $stock->id,
                    (int) $stock->bookId,
                    $bookTitle,
                    (string) $stock->loanEndDate,
                    $returnedAt
                )
            );

            $this->fines->add($fine);

            $fineCreated = [
                'id' => $fine->id,
                'borrowerId' => $fine->borrowerId,
                'amount' => $fine->amount,
                'details' => $fine->details,
                'createdAt' => $fine->createdAt,
            ];
        }

        $stock->isOnLoan = false;
        $stock->loanEndDate = null;
        $stock->borrowerId = null;

        return [
            'message' => 'Book returned',
            'bookStockId' => $bookStockId,
            'returnedAt' => $returnedAt,
            'overdueDays' => $overdueDays,
            'fine' => $fineCreated,
        ];
    }

    private function assertValidReturnInput(int $bookStockId, string $returnedAt): void
    {
        if ($bookStockId <= 0) {
            throw new ApiException(422, 'bookStockId is required', 'VALIDATION_ERROR');
        }

        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $returnedAt);
        $errors = \DateTimeImmutable::getLastErrors();

        if (!$dt || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            throw new ApiException(422, 'returnedAt must be in Y-m-d format', 'VALIDATION_ERROR');
        }
    }

    private function getOnLoanStockOrFail(int $bookStockId): BookStock
    {
        $stock = $this->bookStocks->find($bookStockId);
        if (!$stock) {
            throw new ApiException(404, 'Book stock not found', 'NOT_FOUND');
        }

        if (!$stock->isOnLoan || $stock->borrowerId === null) {
            throw new ApiException(409, 'Book stock is not currently on loan', 'CONFLICT');
        }

        return $stock;
    }

    private function overdueDays(?string $loanEndDate, string $returnedAt): int
    {
        if (!$loanEndDate) {
            return 0;
        }

        $end = \DateTimeImmutable::createFromFormat('Y-m-d', $loanEndDate);
        $ret = \DateTimeImmutable::createFromFormat('Y-m-d', $returnedAt);

        if (!$end || !$ret || $ret <= $end) {
            return 0;
        }

        return (int) $end->diff($ret)->days;
    }
}