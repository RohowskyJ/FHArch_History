<?php
// Fehleranzeige und Logging aktivieren (nur für Debug, im Produktivbetrieb aus)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/EMA_API_php-error.log.txt');

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/CONF_fatal_error.log', $message, FILE_APPEND);
    }
});

// AUTOLOADER für Composer-Klassen laden

$composerAutoload = __DIR__ . '/../../../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    error_log('Composer autoload not found: ' . $composerAutoload);
}

require_once __DIR__ . '/../../Database/DB_GenericLog.php';
#require_once '/FHArch_OopR/src/Core/AllgVerw/API/EMA_ListRepostory.php';
#require_once 'EMA_ListTableConfig.php';

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\AllgVerw\API\EMA_ListRepository;
use Fharch\Core\AllgVerw\API\EMA_ListTableConfig;

// Output Buffering starten, um unerwünschte Ausgabe zu kontrollieren
ob_start();

try {

    header('Content-Type: application/json; charset=utf-8');
    
    $DBD = new DB_GenericLog();
    $pdo = $DBD->getPDO();
    $repo = new CONF_ListRepository($pdo);
    
    // Debug-Ausgabe als Log, nicht als var_dump
    error_log("Repo Objekt: " . print_r($repo, true));
    
    // Parameter aus GET oder POST
    $listType = $_GET['T_List'] ?? 'Alle';
    $search = $_GET['search'] ?? null;
    
    error_log("Suchparameter: " . var_export($search, true));
    
    $data = $repo->getUsers($listType, $search);
    $columns = CONF_ListTableConfig::getColumns($listType, $pdo);
    
    error_log("Columns: " . print_r($columns, true));
    
    $response = [
        'columns' => $columns,
        'data' => $data,
    ];
    
    error_log("Response Array: " . print_r($response, true));
    
    $json = json_encode($response);
    
    if ($json === false) {
        $jsonError = json_last_error_msg();
        error_log("JSON encode error: $jsonError");
        http_response_code(500);
        echo json_encode(['error' => "JSON encode error: $jsonError"]);
        ob_end_flush();
        exit;
    }
    
    // Output Buffer leeren und JSON ausgeben
    ob_end_clean();
    echo $json;
    
} catch (Exception $e) {
    http_response_code(500);
    $errorMsg = $e->getMessage();
    error_log("Exception: $errorMsg");
    echo json_encode(['error' => "Exception: $errorMsg"]);
    ob_end_flush();
}