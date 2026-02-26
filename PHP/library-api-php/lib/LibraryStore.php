<?php

final class LibraryStore
{
    public function __construct(
        public array &$authors,
        public array &$books,
        public array &$borrowers,
        public array &$bookStocks,
        public array &$fines,
        public array &$reservations
    ) {
    }
}