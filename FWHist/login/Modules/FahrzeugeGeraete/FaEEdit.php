<?php

declare(strict_types=1);

/**
 * Motorisierte Fahrzeuge- Eigner- Daten- Wartung
 *
 * @author Josef Rohowsky - neu 2026
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../../login/AOrd_Verz/Logging/FAF_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../../login/AOrd_Verz/Logging/FAF_Edit_php-error.log.txt');


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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\FahrzeugeGeraete\API\DB_FzgGer;
use FWHist\Modules\FahrzeugeGeraete\API\FzgGerTableDefinitions;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Services\HTMLPage;

$module = 'FAF-Edit';
$sub_mod = 'Edit';
$module = "FZG-GER";

$Zugr = 'ADM-FZG';

$debug = False; // Debug output Ein/Aus Schalter


require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
# require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('Mot- Fahrzeuge- Eigner- Daten Verwaltung',
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

if (empty($_SESSION['BS_Prim']['BE'])) {
    http_response_code(401);
    echo 'Anmeldung erforderlich.';
    exit;
}

$ber = userBerechtigtOK($Zugr);
if (!$ber && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (int) ($_POST['phase'] ?? 0) === 1) {
    http_response_code(403);
    echo 'Änderungen sind in dieser Ansicht nicht erlaubt.';
    exit;
}

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();
#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
# var_dump($meta);

$ei_id = isset($_POST['ei_id']) ? intval($_POST['ei_id']) : (isset($_GET['ei_id']) ? intval($_GET['ei_id']) : 0);
$fz_id = isset($_POST['fz_id']) ? intval($_POST['fz_id']) : (isset($_GET['fz_id']) ? intval($_GET['fz_id']) : 0);
$fz_eign_id = isset($_POST['fz_eign_id']) ? intval($_POST['fz_eign_id']) : (isset($_GET['fz_eign_id']) ? intval($_GET['fz_eign_id']) : 0);

#echo __LINE__ . " fz_id $fz_id ei_id $ei_id<br>";
$columnsByTables = $meta->getColumnsForTables([$ei_id . '_fza_fahrzeug', $ei_id . '_fza_eigner' ]);

#var_dump($columnsByTables);
# var_dump($meta);
$fzgger = new DB_FzgGer($DBD, $ei_id);
#var_dump($mitgl);
#var_dump($_SERVER);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================

$phase = isset($_POST['phase']) ? $_POST['phase'] : 0;

if (isset($_POST['proj'])) {
    $proj = $_POST['proj'];
}

#echo __LINE__ . " fz_id $fz_id ei_id $ei_id<br>";
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($fz_eign_id == 0) {
        $neu = [
            'fz_eign_id' => 0,
            'fz_id' => $fz_id,
            "fz_docbez" => "",
            'fz_zul_dat' => '',
            'fz_zul_end_dat' => '',
            'fz_zuldaten' => "",
            'fz_changet_id' =>'',
            'fz_changed_at' => ""
        ];
        
    } else {
        
        $neu = $fzgger->getFaEById($fz_eign_id);
        #  var_dump($neu);
    }
}

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }
    # var_dump($neu);
    date_default_timezone_set('Europe/Berlin');
    
    $neu['fz_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    $neu['fz_changed_at'] =  date('Y-m-d H:m:s');
    
    foreach ( $neu as $key => $val) {
        if (substr($key,0,3) != 'fz_') {
            unset($neu[$key]);
        }
    }
    $neu['fz_eign_id'] = intval($neu['fz_eign_id']);
    FzgGerTableDefinitions::ensureTenantTable($pdo, $ei_id, $ei_id . '_fza_eigner');
    
    if ($neu['fz_eign_id'] === 0) { # neuengabe
        $neu['fz_id'] = $fz_id;
        $ret = $fzgger->insertFaE($neu);
        $neu['fz_eign_id'] = $ret;
    } else { # Update
        unset($neu['fz_id']);
        $ret = $fzgger->updateFaE($neu, ['fz_eign_id' => $fz_eign_id, 'fz_id' => $fz_id] );
    }

    echo "Die Daten wurden gespeichert.<br>";
    echo "Sie werden gleich zurückgeleitet.";
    
    echo "<script>
    setTimeout(function() {
        window.location.href = 'FaEEdit.php?fz_id=$fz_id&ei_id=$ei_id&fz_eign_id=" . $neu['fz_eign_id'] . "';
    }, 2000); // 2 Sekunden warten
    </script>";
    exit;
}

switch ($phase) {
    case 0:
        require ('FaEEdit_ph0_inc.php');
        break;
}

$page->renderFooter();

?>
