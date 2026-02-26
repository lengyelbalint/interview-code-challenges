<?php

require_once __DIR__ . '/../lib/LibraryStore.php';

final class BookStockRepository
{
    public function __construct(private LibraryStore $store)
    {
    }

    /** @return BookStock[] */
    public function all(): array
    {
        return $this->store->bookStocks;
    }

    public function find(int $bookStockId): ?BookStock
    {
        foreach ($this->store->bookStocks as $bookStock) {
            if ((int) $bookStock->id === $bookStockId) {
                return $bookStock;
            }
        }
        return null;
    }

    public function isBookOnLoan(int $bookId): bool
    {
        foreach ($this->store->bookStocks as $bookStock) {
            if ((int) $bookStock->bookId === $bookId && $bookStock->isOnLoan) {
                return true;
            }
        }
        return false;
    }

    public function hasAvailableCopy(int $bookId): bool
    {
        foreach ($this->store->bookStocks as $bookStock) {
            if ((int) $bookStock->bookId === $bookId && !$bookStock->isOnLoan) {
                return true;
            }
        }
        return false;
    }

    public function getEarliestLoanEndDate(int $bookId): ?string
    {
        $min = null;
        foreach ($this->store->bookStocks as $bookStock) {
            if ((int) $bookStock->bookId !== $bookId || !$bookStock->isOnLoan || empty($bookStock->loanEndDate)) {
                continue;
            }
            if ($min === null || strtotime($bookStock->loanEndDate) < strtotime($min)) {
                $min = $bookStock->loanEndDate;
            }
        }
        return $min;
    }
}