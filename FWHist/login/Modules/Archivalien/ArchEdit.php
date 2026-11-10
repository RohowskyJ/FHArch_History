<?php

declare(strict_types=1);

/**
 * Mandanten- Daten- Wartung
 *
 * @author Josef Rohowsky - neu 2026
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

// Fehleranzeige und Logging aktivieren (nur für Debug, im Produktivbetrieb aus)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../../login/AOrd_Verz/Logging/ArchEdit_API_php-error.log.txt');

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../../login/AOrd_Verz/Logging/ArchEdit__fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../../login/AOrd_Verz/Logging/ArchEdit_php-error.log.txt');
# var_dump($_SERVER);
    
# $_SESSION['BS_Prim'] ??= [];
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Auth\AccessGuard;
new EnvSessionManager();

$module = 'ARC';
$sub_mod = 'Edit';

$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Mandanten\API\DB_Mandanten;
use FWHist\Modules\Archivalien\API\DB_Archivalien;
use FWHist\Modules\Archivalien\API\ArchivTableDefinitions;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex_Ext;
use FWHist\Services\HTMLPage;

$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
$isPublicView = AccessGuard::isPublicView();
$phase = (int) ($_POST['phase'] ?? 0);
$adIdParam = $_POST['ad_id'] ?? $_GET['ad_id'] ?? 0;
$ad_id = filter_var($adIdParam, FILTER_VALIDATE_INT);
if ($ad_id === false || $ad_id < 0) {
    AccessGuard::deny('Ungültige Archivalien-ID.', 400);
}
$eiIdParam = $_POST['ei_id'] ?? $_GET['ei_id'] ?? 0;
#var_dump($eiIdParam);
$ei_id = filter_var($eiIdParam, FILTER_VALIDATE_INT);
if ($ei_id === false || $ei_id <= 0) {
    AccessGuard::deny('Ungültiger Mandant.', 400);
}
#var_dump($ei_id);
if ($isPublicView || !$guard->canReadMandantData()) {
    AccessGuard::deny('Anmeldung erforderlich.');
}
if ($phase !== 0 && $phase !== 1) {
    AccessGuard::deny('Ungültige Formularphase.', 400);
}
if ($phase === 1) {
    if (!$guard->canWriteMandantData('ARC', $ei_id)) {
        AccessGuard::deny('Für Änderungen an den Archivalien dieses Mandanten fehlt die Berechtigung.');
    }
    AccessGuard::requireCsrf();
} elseif ($ad_id === 0 && !$guard->canWriteMandantData('ARC', $ei_id)) {
    AccessGuard::deny('Für neue Archivalien dieses Mandanten fehlt die Berechtigung.');
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$proj= isset($_GET['proj']) ? $_GET['proj'] : 'Z';

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$pdo = $DBD->getPDO();

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$tabNam = $ei_id . '_archivdat';
$columnsByTables = $meta->getColumnsForTables([$tabNam , 'hi_archivord', 'hi_archord_erw' ]);
#var_dump($tabNam);
$arch = new DB_Archivalien($DBD, $ei_id);

$mand = new DB_Mandanten($DBD);

$eigent_arr = $mand->getEigentuemerData($ei_id);

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }

    $neu['ad_id'] = $ad_id;
    $neu['ei_id'] = $ei_id;
    if ($ad_id > 0 && $arch->getArchivById($ad_id) === null) {
        AccessGuard::deny('Archivalie nicht gefunden.', 404);
    }
    
    date_default_timezone_set('Europe/Berlin');
    
    $neu['ad_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    $neu['ad_changed_at'] = date('Y-m-d H:i:s');
    
    if (($neu['mand_id'] ?? '') != '') {
        $neu['ad_neueigner'] = intval($neu['mand_id']);
    }
 
    $neu['ad_ao_fortlnr'] = intval($neu['ad_ao_fortlnr']);
    
    if (isset($neu['ad_archivwert'])) {unset($neu['ad_archivwert']);}
    
   # var_dump($neu);
    
    foreach ( $neu as $key => $val) {
        if (substr($key,0,3) != 'ei_' && substr($key,0,3) != 'ad_') {
            unset($neu[$key]);
        }
    }
    
    # var_dump($neu);

    ArchivTableDefinitions::ensureTenantTable($pdo, $ei_id, $tabNam);
    if ($ad_id === 0) { # neuengabe
        $ret = $arch->insertArch($neu);
    } else { # Update
        $ret = $arch->updateArch( $neu, ['ad_id' => $ad_id ] );
    }
    
    header("Location: ArchivList.php?mand_id=$ei_id");
    exit;
}

$header = "";

$page = new HTMLPage('Archivalien- Verwaltung',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
     
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

// ============================================================================================================
// Eingabenerfassung und defa,uls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================

#var_dump($ad_id);
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($ad_id == 0) {
        
        $ao_arr = [0 => "", 1 => '', 2 => "", 3 => '', 4 => "", 5 => '', ];

        $neu = array(
            'ad_id' => 0,
            'ei_id' => "$ei_id",
            "ad_sg" => "$ao_arr[0]",
            'ad_subsg' => "$ao_arr[1]",
            'ad_lcsg' => "$ao_arr[2]",
            'ad_lcssg' => "$ao_arr[3]",
            'ad_lcssg_s0' => "$ao_arr[4]",
            'ad_lcssg_s1' => "$ao_arr[5]",
            "ad_ao_fortlnr" => 0,
            'ad_doc_date' => "",
            "ad_type" => "",
            "ad_format" => "",
            'ad_keywords' => "",
            "ad_beschreibg" => "",
            "ad_wert_orig" => "0",
            "ad_orig_waehrung" => "",
            "ad_wert_kauf" => "0",
            "ad_kauf_waehrung" => "",
            "ad_wert_besch" => "0",
            'ad_besch_waehrung' => "",
            "ad_namen" => "",
            "ad_doc_1" => "",
            "ad_doc_2" => "",
            "ad_doc_3" => "",
            "ad_doc_4" => "",
            "ad_isbn" => "",
            "ad_lagerort" => "",
            'ad_l_raum' => '',
            'ad_l_kasten' => "",
            'ad_l_fach' => "",
            'ad_l_pos_x' => "",
            'ad_l_pos_y' => "",
            'ad_neueigner' => "",
            'ad_changed_id' => 0,
            'ad_changed_at' => ""
        );
    } else {
        # var_dump($ei_id);
        $neu = $arch->getArchivById($ad_id);
        if ($neu === null) {
            AccessGuard::deny('Archivalie nicht gefunden.', 404);
        }
        
        # var_dump($neu);
        
    }
}
$editProtect = !$guard->canWriteMandantData('ARC', $ei_id);


switch ($phase) {
    case 0:
        require ('ArchEdit_ph0_inc.php');
        break;
}
$page->renderFooter();
?>