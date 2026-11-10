<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');

$composerAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (!file_exists($composerAutoload)) {
    error_log('Composer autoload not found: ' . $composerAutoload);
    http_response_code(500);
    echo json_encode(['error' => 'Composer autoload not found']);
    exit;
}
require_once $composerAutoload;

$srv = $_SERVER['HTTP_HOST'] ?? '';
$SI = $srv === 'localhost' ? 'l' : 'fwhist';
require_once __DIR__ . '/../../../login/config/ConfigLib_d_' . $SI . '.php';

use FWHist\Auth\API\BenutzerAutocompleteAPI;

if (trim((string)($_GET['term'] ?? '')) === '') {
    echo json_encode([]);
    exit;
}

try {
    (new BenutzerAutocompleteAPI())->handleRequest();
} catch (Throwable $e) {
    error_log('BenutzerAutocompleteAPI Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Autocomplete konnte nicht ausgeführt werden']);
}
