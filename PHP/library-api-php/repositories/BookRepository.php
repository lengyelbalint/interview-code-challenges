<?php

require_once __DIR__ . '/../lib/LibraryStore.php';

final class BookRepository
{
    public function __construct(private LibraryStore $store)
    {
    }

    /** @return Book[] */
    public function all(): array
    {
        return $this->store->books;
    }

    public function indexById(): array
    {
        $map = [];
        foreach ($this->store->books as $book) {
            $map[(int) $book->id] = $book;
        }
        return $map;
    }

    public function find(int $id): ?Book
    {
        foreach ($this->store->books as $book) {
            if ((int) $book->id === $id) {
                return $book;
            }
        }
        return null;
    }
}