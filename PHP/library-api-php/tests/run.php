<?php

require_once __DIR__ . '/../data/data.php';

require_once __DIR__ . '/../lib/ApiException.php';
require_once __DIR__ . '/../lib/Http/Request.php';
require_once __DIR__ . '/../lib/Http/JsonResponse.php';

require_once __DIR__ . '/../repositories/BookRepository.php';
require_once __DIR__ . '/../repositories/BorrowerRepository.php';
require_once __DIR__ . '/../repositories/BookStockRepository.php';
require_once __DIR__ . '/../repositories/FineRepository.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';

require_once __DIR__ . '/../services/FineCalculator.php';
require_once __DIR__ . '/../services/LoanService.php';
require_once __DIR__ . '/../services/ReservationService.php';
require_once __DIR__ . '/../services/BookService.php';

require_once __DIR__ . '/../controllers/LoanController.php';
require_once __DIR__ . '/../controllers/ReservationController.php';
require_once __DIR__ . '/../controllers/BookController.php';

function assertTrue($cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

function capture(callable $fn): string
{
    ob_start();
    $fn();
    return (string) ob_get_clean();
}

function asJson(string $out): array
{
    $d = json_decode($out, true);
    assertTrue(is_array($d), "Response is not valid JSON: {$out}");
    return $d;
}

function makeApp(): array
{
    library_bootstrap('test');
    $_GET = [];

    $request = new Request();
    $store = library_store();

    $bookRepo = new BookRepository($store);
    $borrowerRepo = new BorrowerRepository($store);
    $bookStockRepo = new BookStockRepository($store);
    $fineRepo = new FineRepository($store);
    $reservationRepo = new ReservationRepository($store);

    $loanService = new LoanService($bookStockRepo, $borrowerRepo, $bookRepo, $fineRepo, new FineCalculator(1.00));
    $reservationService = new ReservationService($bookRepo, $borrowerRepo, $bookStockRepo, $reservationRepo);
    $bookService = new BookService($bookRepo);

    return [
        'loan' => new LoanController($loanService, $request),
        'reservation' => new ReservationController($reservationService, $request),
        'book' => new BookController($bookService),
    ];
}

$app = makeApp();
$out = capture(fn() => $app['book']->index());
$payload = asJson($out);
assertTrue(isset($payload['data']) && is_array($payload['data']), "GET /books should return {data:[]}");
assertTrue($payload['data'][0]['title'] === 'Pride and Prejudice', "Expected seeded book title");

$app = makeApp();
$out = capture(fn() => $app['loan']->index());
$payload = asJson($out);
assertTrue(isset($payload['data']), "GET /loans should return {data:[]}");
assertTrue($payload['data'][0]['borrower']['id'] === 1, "Expected borrowerId=1");
assertTrue($payload['data'][0]['loans'][0]['title'] === 'Pride and Prejudice', "Expected title in loans");

$app = makeApp();
$GLOBALS['__test_raw_body'] = json_encode(['bookStockId' => 1, 'returnedAt' => '2025-04-10']);
$out = capture(fn() => $app['loan']->returnBook());
$payload = asJson($out);
assertTrue($payload['data']['overdueDays'] === 0, "Expected overdueDays=0");
assertTrue($payload['data']['fine'] === null, "Expected no fine");

$app = makeApp();
$GLOBALS['__test_raw_body'] = json_encode(['bookStockId' => 1, 'returnedAt' => '2025-04-12']);
$out = capture(fn() => $app['loan']->returnBook());
$payload = asJson($out);
assertTrue($payload['data']['overdueDays'] === 2, "Expected overdueDays=2");
assertTrue(is_array($payload['data']['fine']), "Expected fine object");

$app = makeApp();
$GLOBALS['__test_raw_body'] = json_encode(['bookId' => 1, 'borrowerId' => 2]);
$out = capture(fn() => $app['reservation']->reserve());
$payload = asJson($out);
assertTrue($payload['data']['reservation']['position'] === 1, "Expected position=1");

$_GET = ['bookId' => 1, 'borrowerId' => 2];
$out = capture(fn() => $app['reservation']->status());
$payload = asJson($out);
assertTrue($payload['data']['position'] === 1, "Expected status position=1");
assertTrue(isset($payload['data']['estimatedAvailableFrom']), "Expected estimatedAvailableFrom");

fwrite(STDERR, "ALL TESTS PASSED\n");