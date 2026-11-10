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


// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../../login/AOrd_Verz/Logging/InvEdit__fatal_error.log', $message, FILE_APPEND);
    }
});

// Fehleranzeige und Logging aktivieren (nur für Debug, im Produktivbetrieb aus)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../../login/AOrd_Verz/Logging/InvEdit_API_php-error.log.txt');

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

use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Mandanten\API\DB_Mandanten;
use FWHist\Modules\Inventar\API\DB_Inventar;
use FWHist\Modules\Inventar\API\InventarTableDefinitions;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex_Ext;
use FWHist\Services\HTMLPage;
use FWHist\Auth\AccessGuard;

$module = 'INV-Edit';
$sub_mod = 'Edit';

$Zugr = 'ADM-INV';

$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

#var_dump($_POST);
#var_dump($_GET);
$proj= isset($_GET['proj']) ? $_GET['proj'] : 'Z';

$in_id = (isset($_GET['in_id']) ? intval($_GET['in_id']) : 0);

$mand_id = $ei_id = isset($_GET['ei_id']) ? intval($_GET['ei_id']) : (isset($_POST['ei_id']) ? intval($_POST['ei_id']) : 0);

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();
$pdo = $DBD->getPDO();

// RBAC: Lesen = jeder gültig angemeldete Benutzer; Speichern nur mit Schreibrecht für genau diesen Mandanten
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canReadMandantData()) {
    http_response_code(403);
    exit('Anmeldung erforderlich.');
}
if ((int) ($_POST['phase'] ?? 0) === 1 && ($ei_id <= 0 || !$guard->canWriteMandantData('INV', $ei_id))) {
    http_response_code(403);
    exit('Für Änderungen am Inventar dieses Mandanten fehlt die Berechtigung.');
}
if ((int) ($_POST['phase'] ?? 0) === 1) {
    AccessGuard::requireCsrf();   // Token aus dem Formular (InvEdit_ph0_inc.php)
}

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$tabNam = $ei_id . '_inventar';
$tabNam_verl =  $ei_id . '_invent_verleih';
$columnsByTables = $meta->getColumnsForTables([$tabNam , $tabNam_verl ]);

$invent = new DB_Inventar($DBD, $ei_id);

$mand = new DB_Mandanten($DBD);

$eigent_arr = $mand->getEigentuemerData($ei_id);

$phase = isset($_POST['phase']) ? $_POST['phase'] : 0;

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }
    
    date_default_timezone_set('Europe/Berlin');
    
    $neu['in_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    
    if (($neu['mand_id'] ?? '') != '') {
        $neu['in_neueigner'] = intval($neu['mand_id']);
    }
 
    if ($neu['in_changed_at'] == '') {
        $neu['in_changed_at'] = date("Y-m-d H:i:s");
    }
    # var_dump($neu);
    
    foreach ( $neu as $key => $val) {
        
        if (substr($key,0,3) != 'in_' && $key != 'ei_id') {
            unset($neu[$key]);
        }
    }
    #var_dump($mand_id);
    #var_dump($ei_id);
    #var_dump($neu);

    $neu['ei_id'] = $ei_id; // nie dem POST-Wert vertrauen: geschrieben wird in die Tabelle von $ei_id
    InventarTableDefinitions::ensureTenantTable($pdo, $ei_id, $tabNam);
    
    if ($neu['in_id'] == 0) { # neuengabe
        $ret = $invent->insertInv($neu);
        #var_dump($ret);
    } else { # Update
        $in_id = $neu['in_id'];
        $ret = $invent->updateInv( $neu, ['in_id' => $in_id ] );
    }
    $ei_id = $neu['ei_id'];
    
    header("Location:  InventarList.php?mand_id=$ei_id");
}

$header = "";

$page = new HTMLPage('Inventar- Verwaltung',
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


# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($in_id == 0) {
        
        $ao_arr = [0 => "", 1 => '', 2 => "", 3 => '', 4 => "", 5 => '', ];

        $neu = array(
            'in_id' =>  0,
            'ei_id' => "$ei_id",
            "in_invjahr" => "",
            'in_eingbuchnr' => "",
            'in_eingbuchdat' => "",
            'in_altbestand' => "",
            "in_invnr" => "",
            "in_sammlg" => "",
            'in_epoche' => "",
            'in_zustand' => "",
            "in_entstehungszeit" => "",
            "in_hersteller" => "",
            'in_bezeichnung' => "",
            "in_beschreibg" => "",
            "in_det_beschrbg" => "",
            "in_wert_neu" => 0,
            "in_neu_waehrg" => "",
            "in_wert_kauf" => 0,
            "in_kauf_waehrung" => "",
            "in_wert_besch" => 0,
            'in_besch_waehrung' => "",
            'in_abmess' => "",
            'in_gewicht' => '',
            'in_linkerkl' => "",
            'in_kommentar' => "",
            'in_namen' => "",
            'in_vwlinks' => "",
            'in_beschreibung' => "",
            'in_foto_1' => "",
            'in_fbeschr_1' => "",
            'in_foto_2' => "",
            'in_fbeschr_2' => "",
            'in_refindex' => "",
            'in_raum' => "",
            'in_platz' => "",
            'in_erstdat' => "",
            'in_ausgdat' => "",
            'in_neueigner' => "",
            'in_uidaend' => "",
            'in_aenddat' => "",
            'sa_name' => "",
            'in_changed_id' => 0,
            'in_changed_at' => ""
        );
    } else {
        # var_dump($in_id);
        $neu = $invent->getInvById($in_id);
        
        if ($neu['ei_id'] == 0) {
            $neu['ei_id'] = $mand_id;
        }
        # var_dump($neu);
        
    }
}


switch ($phase) {
    case 0:
        require ('InvEdit_ph0_inc.php');
        break;
}
$page->renderFooter();
?>