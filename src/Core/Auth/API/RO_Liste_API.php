<?php
// Fehleranzeige und Logging aktivieren (nur für Debug, im Produktivbetrieb aus)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/RO_ListeAPI_php-error.log.txt');

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/fatal_error.log', $message, FILE_APPEND);
    }
});

    $composerAutoload = __DIR__ . '/../../../../vendor/autoload.php';
    if (file_exists($composerAutoload)) {
        require_once $composerAutoload;
    } else {
        error_log('Composer autoload not found: ' . $composerAutoload);
    }

    use Fharch\Core\Database\DB_GenericLog;
    use Fharch\Core\Auth\API\RO_ListeRepository;
    use Fharch\Core\Auth\API\RO_ListeTableConfig;
    
    // Autoloader sollte die Klassen laden - kein require_once nötig
    if (!class_exists(DB_GenericLog::class)) {
        error_log("Class Fharch\\Core\\Database\DB_GenericLog not found.");
    }
    if (!class_exists(Fharch\Core\Auth\API\RO_ListeRepository::class)) {
        error_log("Class Fharch\\Core\\Auth\\API\\RO_ListeRepository not found.");
    }
    if (!class_exists(Fharch\Core\Auth\API\RO_ListeTableConfig::class)) {
        error_log("Class Fharch\\Core\\Auth\\API\\RO_ListeTableConfig not found.");
    }
    
// Output Buffering starten, um unerwünschte Ausgabe zu kontrollieren
ob_start();

try {

    header('Content-Type: application/json; charset=utf-8');
    
    $DBD = new DB_GenericLog();
    $pdo = $DBD->getPDO();
    $repo = new RO_ListeRepository($pdo);
    
    // Debug-Ausgabe als Log, nicht als var_dump
    error_log("Repo Objekt: " . print_r($repo, true));
    
    // Parameter aus GET oder POST
    
    $listType = $_GET['T_List'] ?? 'Alle';
    $search = $_GET['search'] ?? null;
    
    error_log("Suchparameter: " . var_export($search, true));
    
    $data = $repo->getUsersRoles($listType, $search);
    $columns = RO_ListeTableConfig::getColumns($listType, $pdo);
    
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