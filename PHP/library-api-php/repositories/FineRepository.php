<?php

require_once __DIR__ . '/../lib/LibraryStore.php';

final class FineRepository
{
    public function __construct(private LibraryStore $store)
    {
    }

    public function nextId(): int
    {
        $max = 0;
        foreach ($this->store->fines as $fine) {
            $max = max($max, (int) ($fine->id ?? 0));
        }
        return $max + 1;
    }

    public function add(Fine $fine): void
    {
        $this->store->fines[] = $fine;
    }
}