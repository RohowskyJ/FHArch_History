<?php

declare(strict_types=1);

/** Anzeige der Ärmelabzeichen
 *
 * @author Josef Rohowsky - neu 2026
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

# $_SESSION['BS_Prim'] ??= [];
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;

use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Psa\API\DB_Psa;
use FWHist\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

$module = 'PSA-ORT';
$sub_mod = 'Show';

$Zugr = 'Alle';

$debug = False; // Debug output Ein/Aus Schalter

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '../../../../login/AOrd_Verz/Logging/AermDet_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '../../../../login/AOrd_Verz/Logging/AermDet_Edit_php-error.log.txt');

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';


$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('Ärmelabzeichen',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
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

$columnsByTables = $meta->getColumnsForTables(['pso_ort_ref', 'psw_aermel_abz', 'psw_ff_wappen', 'psw_ort_wappen' ]);
#var_dump($columnsByTables);
#var_dump($meta);
$psa = new DB_Psa($DBD);
#var_dump($mitgl);
#var_dump($_SERVER);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
#error_log(__LINE__ . ' ' . date('Y-m-d H:i:s'). ' vor einlese GET '. PHP_EOL, 3, __DIR__ . '/AermDet_Liste_php-error.log.txt');
$phase = isset($_POST['phase']) ? $_POST['phase'] : 0;

$proj= isset($_GET['proj']) ? $_GET['proj'] : '';

$fw_id = isset($_POST['fw_id']) ? intval($_POST['fw_id']) : (isset($_GET['ID']) ? intval($_GET['ID']) : 0);

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
   
$neu = $psa->getOrtById($fw_id);

require ('AermDetail_ph0_inc.php');

$page->renderFooter();