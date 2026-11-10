<?php

declare(strict_types=1);

# session_start();
/**
 * Liste der Archivalien je Archivordnung; Liste 
 * 
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
 * 
 * 
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfen, ob Session gültig ist
if (empty($_SESSION['BS_Prim'])) {
    // Session ungültig → Weiterleitung auf externe URL
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        header('Location: /localhost/Feuerwehr_Archiv/FWHist/');
        exit;
    }
    header('Location: https://www.feuerwehrhistoriker.at/FWHist/');
    exit;
}

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../../login/AOrd_Verz/Logging/CntArchDocList_fatal_error.log', $message, FILE_APPEND);
    }
});
    
// Projekt-/Session-Umgebung initialisieren.
$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
// Composer-Autoloader, bevor eigentliche Klassen verwendet werden.
require $systemBasePath . 'vendor/autoload.php';  

echo "<!Doctype html>";

use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Mandanten\API\DB_Mandanten;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Services\HTMLPage;
use FWHist\Auth\AccessGuard;

  
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/ArchivList_php-error.log.txt');
# var_dump($_SERVER);

$module = 'OEF-ARC';
$sub_mod = "LIST";

$debug = true;

$tabelle = 'oe_museen';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

$guard = AccessGuard::fromSession(new DB_GenericLog());
if (AccessGuard::isPublicView() || !$guard->canReadMandantData()) {
    http_response_code(403);
    exit('Anmeldung erforderlich.');
}
$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header =   "";
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$page = new HTMLPage('Anzahl der Dokumente je Archivordnungs Gruppe ',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '90em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

$proj = "Z";
#var_dump($_POST);
$moduleId = $module."-".$sub_mod;
// Eigene Meldung mit Modulkennung loggen
# $logger->log('Starte Verarbeitung des Moduls', $moduleId, basename(__FILE__));

// XR_Database mit bestehender PDO-Instanz initialisieren
$DBD = new DB_GenericLog();
$pdo = $DBD->getPDO();

$mand = new DB_Mandanten($DBD);

#==================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================

$phase = $_POST['phase'] ?? 0;

#var_dump($_POST);
#echo __LINE__ . " phase $phase </br>";
if ($phase == 0 ) {
    AutoCompForm_Mandant('selection', 'mand', 'mand', '', 'mand_id');
    
    echo "<p>Nach Auswahl des Mandanten klicken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
    
    echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js'></script>";
} else {
    
    // Mandant ID holen (POST bevorzugt vor GET)
    $ei_id = getRequestParam('mand_id');

    if ($ei_id === null) {
        AccessGuard::deny('Kein gültiger Mandant ausgewählt.', 400);
    }
    
    $meta = new TableColumnMetadata($pdo,'fharch_new',false);
   # var_dump($ei_id);
    
    if ($ei_id !== null) {
        // Mandant ist gesetzt und gültig, Liste rendern
        renderList($ei_id);
    } else {
        // Kein Mandant gesetzt, evtl. Fehlermeldung oder Default-Verhalten
        echo "Kein gültiger Mandant ausgewählt.";
    }
}

$page->renderFooter();


/** Functionen */

// Funktion zur sicheren und priorisierten Abfrage von Parametern
function getRequestParam(string $key, int $minValue = 1): ?int {
    $value = $_POST[$key] ?? $_GET[$key] ?? null;
    if ($value !== null && filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => $minValue]]) !== false) {
        return (int)$value;
    }
    return null;
}

function getRequestString(string $key, array $allowedValues = []): ?string {
    $value = $_POST[$key] ?? $_GET[$key] ?? null;
    if ($value !== null && (empty($allowedValues) || in_array($value, $allowedValues, true))) {
        return $value;
    }
    return null;
}


/**
 * Anzeige der  Liste 
 * 
 * @param int  $ei_id Mandanten- Nummer
 */
function renderList($ei_id)  {
    global $basePath, $proj, $module, $meta, $mand;

  //  $forms = new FormRendererFlex($meta, 0,  $neu, [], false, $module );
    
    $tabNam = $ei_id . '_archivdat';
    $columnsByTables = $meta->getColumnsForTables([$tabNam , 'hi_archivord', 'hi_archord_erw' ]);
    
    $eigent_arr = $mand->getEigentuemerData($ei_id);
    # var_dump($eigent_arr);
    $NeuRec = "";

    $ActionScript = '';

    // Listen-Konfiguration serverseitig definiert
    $lists = [
      [
      'id' => 'AD',
      'container' => 'AD-table',
      'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
      'title' => '- Liste',
      'editScript' => $NeuRec,
      'actionScript' => $ActionScript,
      'actionLabel' => '',
      'newLabel' => '',
      'filterSelectFields' => [],
      'filterTextFields' => [],
      'multiSelectFilter' => [
      ],
      'paginationSizes' => [10, 25, 50, 100],
      'showActionColumn' => false,
      ],
    ];
    
    
    // Globales Parameter-Array
    $globalParams = [
    'owner' => $ei_id,
    'user' => $_SESSION['BS_Prim']['BE']['be_id'],
    'project' => $proj,
    'sel_id' => $ei_id ?? null,
    'edit_Protect' => true,
    ];
    
    ?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
console.log('Lists ', lists);
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.globalParams = globalParams;
</script>

<div id="root-AD">
    <h2>Archivalien- Zähl-  Liste</h2>
    <div id="AD-table"></div>
</div>

<script src="<?= $basePath?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath?>/FWHist/js/list-tables.js"></script>

<?php 

} ## End renderList()
?>