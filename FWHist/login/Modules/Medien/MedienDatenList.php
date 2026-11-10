<?php

declare(strict_types=1);

# session_start();
/**
 * Medien- Liste
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

// Projekt-/Session-Umgebung initialisieren.
$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
// Composer-Autoloader, bevor eigentliche Klassen verwendet werden.
require $systemBasePath . 'vendor/autoload.php';  

use FWHist\Core\EnvSessionManager;
use FWHist\Services\FuncsClass;
use FWHist\Services\HTMLPage;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
# use FWHist\Modules\Mitglieder\API;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/ArchivList_fatal_error.log', $message, FILE_APPEND);
    }
});
    
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/ArchivList_php-error.log.txt');
# var_dump($_SERVER);

$module = 'MED-LIST';
$sub_mod = "LIST";

$debug = true;

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

use FWHist\Database\DB_GenericLog;
use FWHist\Auth\AccessGuard;
use FWHist\Modules\Medien\API\DB_Medien;

$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canReadMandantData()) {
    AccessGuard::deny('Anmeldung erforderlich.');
}
$urh_id = filter_var($_GET['urh_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($urh_id === false || $urh_id === null) {
    http_response_code(400);
    exit('Keine gültige Eigentümer-ID übergeben.');
}
$proj = isset($_GET['aufn_dat']) ? $_GET['aufn_dat'] : null;
$canEdit = $guard->canWriteMandantData('MEDIA', $urh_id);
echo "<!Doctype html>";

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header =   "";
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$page = new HTMLPage('Medien Details ',
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

$moduleId = $module."-".$sub_mod;
// Eigene Meldung mit Modulkennung loggen
# $logger->log('Starte Verarbeitung des Moduls', $moduleId, basename(__FILE__));

// XR_Database mit bestehender PDO-Instanz initialisieren
$pdo = $DBD->getPDO();

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";

$list_ID = 'ND';


$urh_id = isset($_GET['urh_id']) ? $_GET['urh_id'] : null;
$proj = isset($_GET['aufn_dat']) ? $_GET['aufn_dat'] : null;

# if (is_null($urh_id) || is_null($proj) ) {echo "Keine gültigen Daten beim Aufruf- Abbruch."; exit; }

renderList($urh_id);
 
$page->renderFooter();


/** Functionen */


/**
 * Anzeige der Medien- Detail- Liste 
 * 
 * @param int  $ei_id urheber- Nummer
 */
function renderList($ei_id)  {
    global $basePath, $proj, $module, $meta, $canEdit, $guard;

    $actionConfig = $guard->listActionConfig($canEdit);

    $NeuRec = "";
    if ($canEdit) {
        # $NeuRec = " &nbsp; &nbsp; &nbsp; <a href='OArchEdit.php?ID=0' > Neuen Datensatz anlegen </a>";
        $NeuRec = "MedDEdit.php?ID=0&urh_id=$ei_id&aufn_dat=$proj";
    }
    $ActionScript = 'MedDEdit.php';
    
    
    // Listen-Konfiguration serverseitig definiert
    $lists = [
    [
    'id' => 'MD',
    'container' => 'MD-table',
    'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
    'title' => '- Liste',
    'editScript' => $NeuRec,
    'actionScript' => $ActionScript,
    'actionLabel' => $actionConfig['actionLabel'],
    'newLabel' => 'Neuen Medien-Datensatz anlegen',
    'filterSelectFields' => [], // ad_sg/ad_subsg/ad_lc* laufen jetzt ausschließlich über 'multiSelectFilter'
       'filterTextFields' => ['md_beschreibg', 'md_namen','md_suchbegr',],
    'multiSelectFilter' => [
        'selector' => 'fw-multi-select[name="AOrd-Auswahl"]',
        'pathFields' => ['md_beschreibg', 'md_namen','md_suchbegr',],
    ],
    'paginationSizes' => [3, 10, 25, 50, 100],
    'showActionColumn' => $actionConfig['showActionColumn'],
    ],
    ];
    
    
    // Globales Parameter-Array
    $globalParams = [
    'owner' => $ei_id,
    'user' => $_SESSION['BS_Prim']['BE']['be_id'] ?? null,
    'project' => $proj,
    'sel_id' => $ei_id ?? null,
    'edit_Protect' => $actionConfig['editProtect'],
    ];
    
    ?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
console.log('Lists ', lists);
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.globalParams = globalParams;
</script>

<div id="root-MD">
    <h2>Medien- Detail- Liste</h2>
    <div id="MD-table"></div>
</div>

<script src="<?= $basePath?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath?>/FWHist/js/list-tables.js"></script>
<script src='<?= $basePath?>/FWHist/login/Services/js/fw_multi_select.js' defer></script>

<?php 

} ## End renderList()
?>