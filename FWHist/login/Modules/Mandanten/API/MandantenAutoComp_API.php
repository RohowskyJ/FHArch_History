<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Fehleranzeige und Logging aktivieren (nur für Debug, im Produktivbetrieb aus)
ini_set('display_errors', 0);   // JSON-Antwort: keine Meldungen in den Body
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../../AOrd_Verz/Logging/MandAutoComp_API_php-error.log.txt');

// Schreibe Debug-Log in den zentralen Logs-Ordner im Projektstamm
$logPath = realpath(__DIR__ . '/../../../AOrd_Verz/Logging');
if ($logPath === false) {
    $logPath = __DIR__ . '/../../../AOrd_Verz/Logging';
    if (!is_dir($logPath)) {
        mkdir($logPath, 0777, true);
    }
    $logPath = realpath($logPath) ?: __DIR__;
}
$logFile = $logPath . '/MandantenAutoComp_API.txt';
ini_set('error_log', $logFile);

// AUTOLOADER für Composer-Klassen laden
$composerAutoload = __DIR__ . '/../../../../vendor/autoload.php';
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
require_once __DIR__ . '/../../../config/ConfigLib_d_' . $SI . '.php';

// RBAC: nur angemeldete Benutzer (nicht auf externen Seiten) - sonst wären die Namen aller Mandanten abrufbar
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$guard = \FWHist\Auth\AccessGuard::fromSession(new \FWHist\Database\DB_GenericLog());
if (!$guard->canReadMandantData() || \FWHist\Auth\AccessGuard::isPublicView()) {
    http_response_code(403);
    echo json_encode(['error' => 'Anmeldung erforderlich.']);
    exit;
}

use FWHist\Modules\Mandanten\API\MandantenAutocompleteAPI;

$debug = isset($_GET['debug']) && in_array(strtolower((string)$_GET['debug']), ['1', 'true', 'on', 'yes'], true);

// Autocomplete Handler für Mandanten mit der zentralen API-Klasse.
$term = $_GET['term'] ?? '';
$term = trim($term);

if ($debug) {
    error_log('MandantenAutoComp_API debug enabled for term=' . $term . ' uri=' . ($_SERVER['REQUEST_URI'] ?? ''));
}

if ($term === '') {
    echo json_encode([]);
    exit;
}

try {
    $autocomplete = new MandantenAutocompleteAPI();
    $autocomplete->handleRequest();
} catch (Exception $e) {
	    error_log('MandantenAutocompleteAPI Error: ' . $e->getMessage());
    /*
    echo json_encode(array_filter($dummyResults, function($item) use ($term) {
        return stripos($item['label'], $term) !== false;
    }));
    */
    http_response_code(500);
    error_log('DB Error: ' . $e->getMessage());
    echo json_encode(['error' => 'Fehler bei der Datenbankabfrage']);
}