<?php

declare(strict_types=1);

# session_start();
/**
 * Inventar- Liste 
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

echo "<!Doctype html>";

use FWHist\Core\EnvSessionManager;
use FWHist\Services\FuncsClass;
use FWHist\Services\HTMLPage;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/InventarList_fatal_error.log', $message, FILE_APPEND);
    }
});
    
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/InventarList_php-error.log.txt');
# var_dump($_SERVER);

$module = 'OEF-INV';
$sub_mod = "LIST";

$Zugr = "ADM-INV" ;

$debug = true;

$tabelle = '_inventar';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

use FWHist\Database\DB_GenericLog;
use FWHist\Auth\Auth;
use FWHist\Auth\AccessGuard;

if (empty($_SESSION['BS_Prim']['BE']['be_id'])) {
    http_response_code(403);
    exit('Anmeldung erforderlich.');
}

// RBAC: Lesen = jeder gültig angemeldete Benutzer; Schreibrecht wird je Mandant in renderList() geprüft
$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canReadMandantData()) {
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
$page = new HTMLPage('Inventar- Daten ',
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

$list_ID = 'IN';

// Prüfen, ob ID 'Abf' übergeben wurde (POST oder GET)
$id = getRequestString('ID', ['Abf']);

if ($id === 'Abf') {
    AutoCompForm_Mandant('selection', 'mand', 'mand', '', 'mand_id');
    
    echo "<p>Nach Auswahl des Mandanten klicken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
    
    echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js'></script>";
} else {
    // Mandant ID holen (POST bevorzugt vor GET)
    $ei_id = getRequestParam('mand_id');
    
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
 * Anzeige der Archivalien- Liste 
 * 
 * @param int  $ei_id Mandanten- Nummer
 */
function renderList($ei_id)  {
    global $basePath, $proj, $module, $meta, $guard;
    $canEdit = $guard->canWriteMandantData('INV', (int) $ei_id);
    $act = $guard->listActionConfig($canEdit);   // Action-Spalte: Anzeigen für alle, Bearbeiten nur mit Recht

    /** multi-Select-Aufruf Archiv- Ordnung */
    
    $columnsByTables = $meta->getColumnsForTables([$ei_id.'_inventar', $ei_id.'invent_verl' ]);

    $NeuRec = "";
    if ($canEdit) {
        # $NeuRec = " &nbsp; &nbsp; &nbsp; <a href='OArchEdit.php?ID=0' > Neuen Datensatz anlegen </a>";
        $NeuRec = "InvEdit.php?in_id=0&ei_id=$ei_id";
    }
    $ActionScript = 'InvEdit.php';
    
    
    // Listen-Konfiguration serverseitig definiert
    $lists = [
    [
    'id' => 'IN',
    'container' => 'IN-table',
    'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
    'title' => '- Liste',
    'editScript' => $NeuRec,
    'actionScript' => $ActionScript,
    'actionLabel' => $act['actionLabel'],
    'showActionColumn' => $act['showActionColumn'],
    'newLabel' => 'Neue Archiv- Daten anlegen',
    'filterSelectFields' => [], 
        'filterTextFields' => ['in_sammlg', 'in_bezeichnung',  ],
    'multiSelectFilter' => [
        'selector' => 'fw-multi-select[name="AOrd-Auswahl"]',
        'pathFields' => [],
    ],
    'paginationSizes' => [10, 25, 50, 100],
    ],
    ];
    
    
    // Globales Parameter-Array
    $globalParams = [
    'owner' => $ei_id,
    'user' => $_SESSION['BS_Prim']['BE']['be_id'],
    'project' => $proj,
    'sel_id' => $ei_id ?? null,
    'edit_Protect' => $act['editProtect'],
    ];
    
    ?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
console.log('Lists ', lists);
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.globalParams = globalParams;
</script>

<div id="root-IN">
    <h2>Inventar- Liste</h2>
    <div id="IN-table"></div>
</div>

<script src="<?= $basePath?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath?>/FWHist/js/list-tables.js"></script>
<script src='<?= $basePath?>/FWHist/login/Services/js/fw_multi_select.js' defer></script>

<?php 

} ## End renderList()
?>