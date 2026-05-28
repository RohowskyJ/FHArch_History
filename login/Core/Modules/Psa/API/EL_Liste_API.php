<?php
// Fehleranzeige und Logging aktivieren (nur für Debug, im Produktivbetrieb aus)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '../../../../login/AOrd_Verz/Logging/EB_Liste_API_php-error.log.txt');

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../../../login/AOrd_Verz/Logging/EB_Liste_fatal_error.log', $message, FILE_APPEND);
    }
});

    // AUTOLOADER für Composer-Klassen laden
    
    $composerAutoload = __DIR__ . '/../../../../../vendor/autoload.php';
    if (file_exists($composerAutoload)) {
        require_once $composerAutoload;
    } else {
        error_log('Composer autoload not found: ' . $composerAutoload);
    }
    
use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Modules\Psa\API\EB_ListRepository;
use Fharch\Core\Modules\Psa\API\EB_ListTableConfig;

require_once 'EB_ListRepository.php';
require_once 'EB_ListTableConfig.php';

// Output Buffering starten, um unerwünschte Ausgabe zu kontrollieren
ob_start();
try {

    header('Content-Type: application/json; charset=utf-8');
    
    $dbLogging = new DB_GenericLog();
    $pdo = $dbLogging->getPDO();
    $repo = new EB_ListRepository($pdo);
  
    
    // Debug-Ausgabe als Log, nicht als EB_dump
    # error_log("Repo Objekt: " . print_r($repo, true));
    
    // Parameter aus GET oder POST
    $listType = $_GET['T_List'] ?? 'Alle';
    $search = $_GET['search'] ?? null;
    $proj = $_GET['proj_ID'] ?? 'AERM';  // proj = EHRG | AERM
    
    $data = $repo->getAermAbz($listType, $proj, $search);
    #error_log('Search '. var_export($search, true));
    #error_log('ListType '. var_export($listType, true));
    $columns = EB_ListTableConfig::getColumns($listType, $pdo);
    
    # error_log("Columns: " . print_r($columns, true));
    
    $response = [
        'columns' => $columns,
        'data' => $data,
    ];
    
    # error_log("Response Array: " . print_r($response, true));
    
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