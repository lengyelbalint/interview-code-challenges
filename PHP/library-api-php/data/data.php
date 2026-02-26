<?php

require_once __DIR__ . '/../models/Author.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Borrower.php';
require_once __DIR__ . '/../models/BookStock.php';
require_once __DIR__ . '/../models/Fine.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../lib/LibraryStore.php';

function library_seed_demo(): array
{
    $authors = [
        new Author(1, 'Jane Austen'),
        new Author(2, 'Mark Twain'),
        new Author(3, 'George Orwell'),
    ];

    $books = [
        new Book(1, 'Pride and Prejudice', 1, 'Hardcover', '1111111111111'),
        new Book(2, 'Adventures of Huckleberry Finn', 2, 'Paperback', '2222222222222'),
        new Book(3, '1984', 3, 'Paperback', '3333333333333'),
    ];

    $borrowers = [
        new Borrower(1, 'Alice', 'alice@example.com'),
        new Borrower(2, 'Bob', 'bob@example.com'),
        new Borrower(3, 'Carol', 'carol@example.com'),
    ];

    $bookStocks = [
        new BookStock(1, 1, true, '2025-04-10', 1),
        new BookStock(2, 2, false),
        new BookStock(3, 3, true, '2025-03-01', 2),
    ];

    $fines = [];

    $reservations = [
        new Reservation(1, 1, 2, '2026-02-01T10:00:00+01:00', 'active'),
        new Reservation(2, 1, 3, '2026-02-01T10:05:00+01:00', 'active'),
    ];

    return compact('authors', 'books', 'borrowers', 'bookStocks', 'fines', 'reservations');
}

function library_seed_test(): array
{
    $authors = [new Author(1, 'Jane Austen')];

    $books = [
        new Book(1, 'Pride and Prejudice', 1, 'Hardcover', '1111111111111'),
    ];

    $borrowers = [
        new Borrower(1, 'Alice', 'alice@example.com'),
        new Borrower(2, 'Bob', 'bob@example.com'),
    ];

    $bookStocks = [
        new BookStock(1, 1, true, '2025-04-10', 1),
    ];

    $fines = [];
    $reservations = [];

    return compact('authors', 'books', 'borrowers', 'bookStocks', 'fines', 'reservations');
}

function library_bootstrap(string $profile = 'demo'): void
{
    $seed = ($profile === 'test') ? library_seed_test() : library_seed_demo();

    $GLOBALS['authors'] = $seed['authors'];
    $GLOBALS['books'] = $seed['books'];
    $GLOBALS['borrowers'] = $seed['borrowers'];
    $GLOBALS['bookStocks'] = $seed['bookStocks'];
    $GLOBALS['fines'] = $seed['fines'];
    $GLOBALS['reservations'] = $seed['reservations'];

    $GLOBALS['__bootstrapped'] = true;
}

if (!isset($GLOBALS['__bootstrapped'])) {
    library_bootstrap(getenv('LIBRARY_SEED_PROFILE') ?: 'demo');
}

function library_store(): LibraryStore
{
    return new LibraryStore(
        $GLOBALS['authors'],
        $GLOBALS['books'],
        $GLOBALS['borrowers'],
        $GLOBALS['bookStocks'],
        $GLOBALS['fines'],
        $GLOBALS['reservations']
    );
}