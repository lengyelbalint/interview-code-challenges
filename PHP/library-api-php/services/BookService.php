<?php

require_once __DIR__ . '/../repositories/BookRepository.php';

final class BookService
{
    public function __construct(private BookRepository $books)
    {
    }

    public function listBooks(): array
    {
        $books = [];
        foreach ($this->books->all() as $book) {
            $books[] = [
                'id' => $book->id,
                'title' => $book->title,
                'authorId' => $book->authorId,
                'format' => $book->format,
                'isbn' => $book->isbn,
            ];
        }
        return $books;
    }
}