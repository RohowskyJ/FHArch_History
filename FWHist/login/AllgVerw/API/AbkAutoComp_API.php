<?php

use FWHist\Database\DB_GenericLog;

header('Content-Type: application/json; charset=utf-8');

// Fehleranzeige und Logging aktivieren (nur für Debug, im Produktivbetrieb aus)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/Err_AbkAutoComp_API_php-error.log.txt');

// Schreibe Debug-Log in den zentralen Logs-Ordner im Projektstamm
$logPath = realpath(__DIR__ . '/../../../../logs');
if ($logPath === false) {
    $logPath = __DIR__ . '/../../../../logs';
    if (!is_dir($logPath)) {
        mkdir($logPath, 0777, true);
    }
    $logPath = realpath($logPath) ?: __DIR__;
}
$logFile = $logPath . '/AbkAutoComp_API.log.txt';
ini_set('error_log', $logFile);

// AUTOLOADER für Composer-Klassen laden
$composerAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    error_log('Composer autoload not found: ' . $composerAutoload);
    http_response_code(500);
    echo json_encode(['error' => 'Composer autoload not found']);
    exit;
}

// Konfiguration laden (wie in anderen APIs)
$srv = $_SERVER['HTTP_HOST'] ?? '';
$SI = $srv === 'localhost' ? 'l' : 'fwhist';
require_once __DIR__ . '/../../../login/config/ConfigLib_d_' . $SI . '.php';

use FWHist\AllgVerw\API\AbkAutocompleteAPI;

$debug = isset($_GET['debug']) && in_array(strtolower((string)$_GET['debug']), ['1', 'true', 'on', 'yes'], true);

// Autocomplete Handler für Abkürzungen mit der neuen API-Klasse
$term = $_GET['term'] ?? '';
$term = trim($term);

if ($debug) {
    error_log('AbkAutoComp_API debug enabled for term=' . $term . ' uri=' . ($_SERVER['REQUEST_URI'] ?? '')); 
}

if ($term === '') {
    echo json_encode([]);
    exit;
}

try {
    $autocomplete = new AbkAutocompleteAPI();
    $autocomplete->handleRequest();
} catch (Throwable $e) {
    error_log('AbkAutocompleteAPI Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Autocomplete konnte nicht ausgeführt werden']);
}