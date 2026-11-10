<?php

declare(strict_types=1);

# session_start();
/**
 * Orts- Liste als Basis für Ehrenzeichen und Ärmelwappen
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

echo "<!Doctype html>´";

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
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/Orts_List_php-error.log.txt');
# var_dump($_SERVER);

$module = 'OEF-AN';
$sub_mod = "LIST";

$Zugr = 'Alle';

$debug = true;

$tabelle = 'oe_museen';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';


use FWHist\Database\DB_GenericLog;
use FWHist\Auth\Auth;

$ber = userBerechtigtOK($Zugr);
if (!$ber) {
    header("Location $systemBasePath/");
    exit;
}
$isPublicView = ($_SESSION['BS_Prim']['Mod']['smod'] ?? '') === 'index';
$canEdit = !$isPublicView && !empty($_SESSION['BS_Prim']['BE']['be_id']) && userHasRole('ADM-PSA');

$proj = $_GET['proj'] ?? null;  // proj = EHRZ | AERM

if (is_null($proj)) { echo "Fehler: falsche Parameter- Übergabe Orts- Auswahl für PSA. Abbruch. "; exit; }
echo __LINE__ . " proj $proj <br>";
$message = __LINE__ . " vor Abfrage ";

$Kennung = ['AERM' => 'Ärmelabzeichen', 'EHRZ' => 'Ehrenzeichen'];
$ProjAnz = " für " . $Kennung[$proj];

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header =   "";
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$page = new HTMLPage('Ortsauswahl ' . $ProjAnz,
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '90em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

if ($Zugr == 'Alle') {
   
    # file_put_contents('ortlist_error.log', $message, FILE_APPEND);
} else {
    if (isset($_SESSION['BS_Prim']['BE'])) {
        $ber = userBerechtigtOK($Zugr);

        if (!$ber) {
            $bPath = $_SESSION['BS_Prim']['Env']['basePath'];
            echo "Zugriff nicht erlaubt. Abbruch. ";
            $bPath = "";
            if ($_SESSION['BS_Prim']['Env']['basePath'] != "") {
                $bPath = "/" . $_SESSION['BS_Prim']['Env']['basePath'];
            }

            echo '<script type="text/javascript">
                   setTimeout(function() {
                   window.location.href = "<?php echo $bPath ?>/FWHist";
                   }, 2000);
                 </script>';
            return;
            
        }
        
    }
}

$moduleId = $module."-".$sub_mod;
// Eigene Meldung mit Modulkennung loggen
# $logger->log('Starte Verarbeitung des Moduls', $moduleId, basename(__FILE__));

// XR_Database mit bestehender PDO-Instanz initialisieren
$DBD = new DB_GenericLog();
# var_dump($DBD);
$pdo = $DBD->getPDO();
# var_dump($pdo);

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";

$list_ID = 'OR';

if ($proj == 'AERM') {  // Anzeige der Liste für Ärmelabzeichen
    $lTitel = ["Alle" => "Alle Orte "];
    if ($Zugr == 'Alle') {
        $lTitel = ["Alle." => "Alle Orte "];
    }
} else { // Anzeige der Liste für Ehrungen
    /*
    if (!isset($_SESSION['BS_Prim']['BE'])) {
        echo "Zugriff nicht erlaubt. Abbruch. ";
        $bPath = $_SESSION['BS_Prim']['Env']['basePath'];
        echo '<script type="text/javascript">
        setTimeout(function() {
          window.location.href = "/fharch-historie/FWHist";
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
}

$NeuRec = "";
if ($canEdit) {
    # $NeuRec = " &nbsp; &nbsp; &nbsp; <a href='OrtsEdit.php?ID=0' > Neuen Datensatz anlegen </a>";
    $NeuRec = "OrtsEdit.php?ID=0&proj=$proj";
}
$ActionScript = $canEdit ? ($proj === 'AERM'
    ? 'OrtsEdit.php?proj=AERM'
    : 'OrtsEdit.php?proj=EHRZ') : '';


// Listen-Konfiguration serverseitig definiert
$lists = [
    [
        'id' => 'ORT',
        'container' => 'ORTS-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Orts-Liste zur Auswahl',
        'editScript' => $NeuRec,
        'actionScript' => $ActionScript,
        'actionLabel' => 'Bearbeiten',
        'newLabel' => 'Neuen Ort anlegen',
        'showActionColumn' => $canEdit,
        'filterSelectFields' => ['st_abkzg', 'bl_blabkz'],
        'filterTextFields' => ['fw_bz_name', 'fw_gd_name', 'fw_fw_name'],
        'paginationSizes' => [10, 25, 50, 100],
    ],
];


// Globales Parameter-Array
$globalParams = [
    'owner' => 'Muster-Eigentümer',
    'user' => 'Max Mustermann',
    'project' => $proj,
    'sel_id' => $neu['fw_id'] ?? 99,
    'edit_Protect' => !$canEdit,
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
console.log('Lists ', lists);
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.globalParams = globalParams;
</script>

<div id="root-ORT">
    <h2>Orts-Liste zur Auswahl</h2>
    <div id="ORTS-table"></div>
</div>

<script src="<?= $basePath?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath?>/FWHist/js/list-tables.js"></script>

<?php 
$page->renderFooter();

?>