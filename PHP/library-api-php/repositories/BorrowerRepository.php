<?php

require_once __DIR__ . '/../lib/LibraryStore.php';

final class BorrowerRepository
{
    public function __construct(private LibraryStore $store)
    {
    }

    public function indexById(): array
    {
        $map = [];
        foreach ($this->store->borrowers as $borrower) {
            $map[(int) $borrower->id] = $borrower;
        }
        return $map;
    }

    public function find(int $id): ?Borrower
    {
        foreach ($this->store->borrowers as $borrower) {
            if ((int) $borrower->id === $id) {
                return $borrower;
            }
        }
        return null;
    }

    public function exists(int $id): bool
    {
        return $this->find($id) !== null;
    }
}