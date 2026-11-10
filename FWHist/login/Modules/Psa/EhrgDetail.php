<?php
/** Anzeige der Ehrungen */
declare(strict_types=1);

/**
 * Ehrenzeiche-, Wartung
 *
 * @author Josef Rohowsky - neu 2026   orig fv_PS_OV_O_Edit.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

# $_SESSION['BS_Prim'] ??= [];
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
new EnvSessionManager();

$module = 'OEF-MU';
$sub_mod = 'Edit';

$Zugr = "ADM-OEF" ;

$debug = False; // Debug output Ein/Aus Schalter

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MU_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});
    
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/MU_Edit_php-error.log.txt');
    
    require $systemBasePath . 'login/Services/FuncsLib.php';
    require $systemBasePath . 'login/Services/CommFuncsLib.php';
    require $systemBasePath . 'login/Services/ConstLib.php';
    
    use FWHist\Database\DB_GenericLog;
    use FWHist\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;
    use FWHist\Services\TableColumnMetadata;
    use FWHist\Services\FormRendererFlex;
    use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
    use FWHist\Services\HTMLPage;
    
    $envManager = new EnvSessionManager();
    $basePath = $envManager->getEnv('basePath')  ;
    $configFileS = $envManager->getEnv('configFileS');
    
    $header = "";
    
    $page = new HTMLPage('Museen',
        $systemBasePath,
        $basePath,
        ['headExtras' => $header,
            'type' => 'Form',
            'width' => '60em',
            'jq' => true,
            'jqui' => true,
            'jq_accordion' => true,
        ]
        );
    
    $page->loadConfig($configFileS);
    $page->renderHeader();
    
    initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES
    
    
    // ============================================================================================================
    // Eingabenerfassung und defauls
    // ============================================================================================================
    
    $DBD = new DB_GenericLog();
    #var_dump($DBD);
    $pdo = $DBD->getPDO();
    #var_dump($pdo);
    
    $meta = new TableColumnMetadata($pdo,'fharch_new',false);
    #var_dump($meta);
    
    $columnsByTables = $meta->getColumnsForTables(['oe_museen' ]);
    #var_dump($columnsByTables);
    #var_dump($meta);
    $links = new DB_Oeffentlich($DBD);
    #var_dump($links);
    #var_dump($_SERVER);
    // ============================================================================================================
    // Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
    // ============================================================================================================
    if (isset($_POST['phase'])) {
        $phase = $_POST['phase'];
    } else {
        $phase = 0;
    }
    if (isset($_GET['ID'])) {
        $mu_id = intval($_GET['ID']);
    } else {
        $mu_id = 0;
    }
    if (isset($_POST['mu__id'])) {
        $mu_id = intval($_POST['mu_id']);
    }
    