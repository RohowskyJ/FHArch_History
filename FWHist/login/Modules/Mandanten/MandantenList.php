<?php

declare(strict_types=1);

# session_start();
/**
 * Mandanten- Liste 
 * 
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
 * 
 * 
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
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
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
// Composer-Autoloader, bevor eigentliche Klassen verwendet werden.
require $systemBasePath . 'vendor/autoload.php';  

use FWHist\Core\EnvSessionManager;
use FWHist\Services\FuncsClass;
use FWHist\Services\HTMLPage;
use FWHist\Modules\Mitglieder\API;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/Orts_List_fatal_error.log', $message, FILE_APPEND);
    }
});
    
error_reporting(E_ALL);
ini_set('display_errors', '0');   // im Betrieb keine Fehlermeldungen an den Browser
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/Orts_List_php-error.log.txt');
# var_dump($_SERVER);

$module = 'ADM-MAND';
$sub_mod = "LIST";

$debug = false;

$tabelle = 'oe_museen';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';


use FWHist\Database\DB_GenericLog;
use FWHist\Auth\AccessGuard;

// RBAC: Mandanten-Stammdaten nur für ADM-MA (und ADM-ALLE) - VOR jeder Ausgabe
$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canEditMandantStamm()) {
    AccessGuard::deny('Für die Mandanten-Stammdaten ist die Berechtigung ADM-MA erforderlich.');
}
$canEdit = true;   // Seite ist nur für ADM-MA/ADM-ALLE erreichbar
$act = $guard->listActionConfig($canEdit);   // Action-Spalte


$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header =   "";
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$page = new HTMLPage('Mandanten ',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '90em',
        'jq' => false,
        'jqui' => false,
        'jq_accordion' => false,
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
# var_dump($pdo);

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";

$list_ID = 'MA';


    /*
    if (!isset($_SESSION['BS_Prim']['BE'])) {
        echo "Zugriff nicht erlaubt. Abbruch. ";
        $bPath = $_SESSION['BS_Prim']['Env']['basePath'];
        echo '<script type="text/javascript">
        setTimeout(function() {
          window.location.href = "/fharch-oop/VFH";
        }, 400);
      </script>';
            return;
    }
    */
    $lTitel = ["Alle" => "Alle Ehrungen ",
               "Bund" => "vom Bund (Staat) ausgegebenen Auszeichnungen ",
               "Bdld" => "vom Bundesland ausgegebenen Auszeichnungen ",
               "Gde"  => "von einer Gemeinde ausgegebenen Auszeichnungen ",
               "Vereine" => "von einem Verein ausgegebenen Auszeichnungen ",
              ];


$NeuRec = $canEdit ? 'MandEdit.php?ID=0' : '';
$ActionScript = 'MandEdit.php';


// Listen-Konfiguration serverseitig definiert
$lists = [
    [
        'id' => 'MA',
        'container' => 'MA-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Mandanten- Liste',
        'editScript' => $NeuRec,
        'actionScript' => $ActionScript,
        'actionLabel' => $act['actionLabel'],
        'showActionColumn' => $act['showActionColumn'],
        'newLabel' => 'Neuen Mandanten anlegen',
        'filterSelectFields' => ['ei_staat', 'ei_bdld'],
        'filterTextFields' => ['ei_org_name', 'ei_name'],
        'paginationSizes' => [10, 25, 50, 100],
    ],
];


// Globales Parameter-Array
$globalParams = [
    'owner' => '',
    'user' => $_SESSION['BS_Prim']['BE']['be_id'] ?? null,
    'project' => $proj,
    'sel_id' => null,
    'edit_Protect' => $act['editProtect'],
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
console.log('Lists ', lists);
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.globalParams = globalParams;
</script>

<div id="root-MA">
    <h2>Mandanten- Liste</h2>
    <div id="MA-table"></div>
</div>

<script src="<?= $basePath?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath?>/FWHist/js/list-tables.js"></script>

<?php 
$page->renderFooter();

?>