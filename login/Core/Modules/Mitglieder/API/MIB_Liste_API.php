<?php
// Fehleranzeige und Logging aktivieren (nur für Debug, im Produktivbetrieb aus)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/MIB_Liste_API_php-error.log.txt');

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MIB_API_fatal_error.log', $message, FILE_APPEND);
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
use Fharch\Core\Modules\Mitglieder\API\MIB_MemberRepository;
use Fharch\Core\Modules\Mitglieder\API\MIB_MemberTableConfig;

// Output Buffering starten, um unerwünschte Ausgabe zu kontrollieren
ob_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $DBD = new DB_GenericLog();
    $pdo = $DBD->getPDO();
    $repo = new MIB_MemberRepository($pdo);

    // Parameter aus GET oder POST
    $listType = $_GET['T_List'] ?? 'Alle';
    $search = $_GET['search'] ?? null;
    
    $data = $repo->getMembers($listType, $search);
    $columns = MIB_MemberTableConfig::getColumns($listType, $pdo);
    
    $response = [
        'columns' => $columns,
        'data' => $data,
    ];
    
    $json = json_encode($response);
    
    if ($json === false) {
        $jsonError = json_last_error_msg();
        error_log("JSON encode error: $jsonError");
        http_response_code(500);
        echo json_encode(['error' => "JSON encode error: $jsonError"]);
        ob_end_flush();
        exit;
    }
    
    // Vor dem Ausgeben den Output Buffer leeren, um unerwünschte Ausgabe zu vermeiden
    ob_end_clean();
    
    echo $json;
    
} catch (Exception $e) {
    http_response_code(500);
    $errorMsg = $e->getMessage();
    error_log("Exception: $errorMsg");
    echo json_encode(['error' => "Exception: $errorMsg"]);
}

ob_end_flush();