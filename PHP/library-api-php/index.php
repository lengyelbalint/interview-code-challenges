<?php

require_once __DIR__ . '/data/data.php';

require_once __DIR__ . '/lib/ApiException.php';
require_once __DIR__ . '/lib/LibraryStore.php';
require_once __DIR__ . '/lib/Http/Request.php';
require_once __DIR__ . '/lib/Http/JsonResponse.php';

require_once __DIR__ . '/repositories/BookRepository.php';
require_once __DIR__ . '/repositories/BorrowerRepository.php';
require_once __DIR__ . '/repositories/BookStockRepository.php';
require_once __DIR__ . '/repositories/FineRepository.php';
require_once __DIR__ . '/repositories/ReservationRepository.php';

require_once __DIR__ . '/services/FineCalculator.php';
require_once __DIR__ . '/services/LoanService.php';
require_once __DIR__ . '/services/BookService.php';
require_once __DIR__ . '/services/ReservationService.php';

require_once __DIR__ . '/controllers/LoanController.php';
require_once __DIR__ . '/controllers/BookController.php';
require_once __DIR__ . '/controllers/ReservationController.php';

$request = new Request();
$store = library_store();

$bookRepo = new BookRepository($store);
$borrowerRepo = new BorrowerRepository($store);
$bookStockRepo = new BookStockRepository($store);
$fineRepo = new FineRepository($store);
$reservationRepo = new ReservationRepository($store);

$loanService = new LoanService(
    $bookStockRepo,
    $borrowerRepo,
    $bookRepo,
    $fineRepo,
    new FineCalculator(1.00)
);

$bookService = new BookService($bookRepo);

$reservationService = new ReservationService(
    $bookRepo,
    $borrowerRepo,
    $bookStockRepo,
    $reservationRepo
);

$loanController = new LoanController($loanService, $request);
$bookController = new BookController($bookService);
$reservationController = new ReservationController($reservationService, $request);

$uri = $request->path();
$method = $request->method();

if ($uri === '/books' && $method === 'GET') {
    $bookController->index();
} elseif ($uri === '/loans' && $method === 'GET') {
    $loanController->index();
} elseif ($uri === '/loans/return' && $method === 'POST') {
    $loanController->returnBook();
} elseif ($uri === '/reservations' && $method === 'POST') {
    $reservationController->reserve();
} elseif ($uri === '/reservations' && $method === 'GET') {
    $reservationController->status();
} else {
    JsonResponse::error('Endpoint not found', 404);
}