<?php

declare(strict_types=1);

/**
 * Orts- Daten- Wartung
 *
 * @author Josef Rohowsky - neu 2020
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

# $_SESSION['BS_Prim'] ??= [];
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Auth\AccessGuard;
use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Psa\API\DB_Psa;
#use FWHist\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
#use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

$module = 'PSA-ORT';
$sub_mod = 'Edit';

$Zugr = 'ADM-PSA';

$debug = False; // Debug output Ein/Aus Schalter

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../login/AOrd_Verz/Logging/Orts_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});
    
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../login/AOrd_Verz/Logging/Orts_Edit_php-error.log.txt');

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';



$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');


// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();
#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
# var_dump($meta);

$columnsByTables = $meta->getColumnsForTables(['pso_ort_ref', 'psw_aermel_abz', 'psw_ff_wappen', 'psw_ort_wappen' ]);
#var_dump($columnsByTables);
# var_dump($meta);
$psa = new DB_Psa($DBD);
#var_dump($mitgl);
#var_dump($_SERVER);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================

$phase = isset($_POST['phase']) ? $_POST['phase'] : 0;
/*
$fw_id = isset($_GET['ID']) ? $_GET['ID'] : 0;

if (isset($_POST['fw_id'])) {
    $fw_id = $_POST['fw_id'];
}
*/
$proj= isset($_GET['proj']) ? $_GET['proj'] : 0;

if (isset($_POST['proj'])) {
    $proj = $_POST['proj'];
}
$proj = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $proj);   // geht in HTML und Links ein

$fw_id = isset($_POST['fw_id']) ? intval($_POST['fw_id']) : (isset($_GET['ID']) ? intval($_GET['ID']) : 0);
// RBAC - VOR jeder Ausgabe
$guard = AccessGuard::fromSession($DBD);
$isPublicView = AccessGuard::isPublicView();
// Ansehen: intern jeder Angemeldete (schreibgeschützt), extern nur freigegebene Module der Whitelist
if (!$guard->canViewEditForm('ORT', $isPublicView)) {
    AccessGuard::deny('Keine Berechtigung.');
}
$canEdit = $guard->canEditPsa();   // false in der externen Ansicht
if (!$canEdit && ((int) $phase === 1 || $fw_id === 0)) {
    AccessGuard::deny($fw_id === 0 ? 'Zum Anlegen eines Ortes ist die Berechtigung ADM-PSA erforderlich.' : 'Änderungen sind nicht erlaubt.');
}
if ((int) $phase === 1) {
    AccessGuard::requireCsrf();   // Token aus dem Formular (OrtsEdit_ph0_inc.php)
}

$header = "";

$page = new HTMLPage('Orts- Daten- Verwaltung',
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

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($fw_id == 0) {
        $neu = [
        'fw_id' => 0,
        'fw_st_abk' => "AT",
        "fw_bd_abk" => "NOE",
        'fw_bz_abk' => "",
        'fw_bz_name' => "",
        'fw_ab_nr' => "",
        'fw_ab_name' => "",
        'fw_gd_nr' => "",
        "fw_gd_name" => "",
        'fw_gd_art' => "",
        'fw_fw_nr' => "",
        'fw_fw_name' => "",
        'fw_fw_typ' => "",
        'fw_grdg_dat' => "",
        'fw_end_dat' => "",
        'fw_kommentar' => "",
        'fw_auszeich' => '',
        'fw_aermelw' => '',
        'fw_ort_komm' => "",
        "fw_aenddat" => "",
        'fw_changed_id' => '',
        'fw_changed_at' => ""
        ];
    } else {
        
        $neu = $psa->getOrtById($fw_id);
        
        $neu['staat_id'] = '';
        $neu['staat'] = ""; //Auslesen!
        
    }
}

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }
    
    date_default_timezone_set('Europe/Berlin');
    
    $neu['fw_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    
    if ($neu['staat_id'] != '') {
        $neu['st_abkzg'] = $neu['staat_id'];
    }
    if ($neu['bdld_id'] != '') {
        $neu['fbl_blabkz'] = $neu['bdld_id'];
    }
 
    foreach ( $neu as $key => $val) {
        if (substr($key,0,3) != 'fw_') {
            unset($neu[$key]);
        }
    }
    
    $neu['fw_id'] = intval($neu['fw_id']);
    
    if ($neu['fw_id'] == 0) { # neuengabe
        $ret = $psa->insertOrt($neu);
    } else { # Update
        $ret = $psa->updateOrt($neu['fw_id'] , $neu);
    }
    
    header("Location:  OrtsAuswList.php?proj=" . rawurlencode($proj));
    exit;
}

switch ($phase) {
    case 0:
        $editProtect = !$canEdit;
        require ('OrtsEdit_ph0_inc.php');
        break;
}
$page->renderFooter();
?>