<?php

declare(strict_types=1);

/**
 * Medien- Daten- Detail Wartung
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
ini_set('error_log', __DIR__ . '/../../../login/AOrd_Verz/Logging/MedDEdit_API_php-error.log.txt');

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../../login/AOrd_Verz/Logging/MeddEdit__fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../../login/AOrd_Verz/Logging/MedDEdit_php-error.log.txt');
# var_dump($_SERVER);
   
    
# $_SESSION['BS_Prim'] ??= [];
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
new EnvSessionManager();
use FWHist\Auth\AccessGuard;

$module = 'MEDIA';
$sub_mod = 'Edit';

$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Mandanten\API\DB_Mandanten;
use FWHist\Modules\Medien\API\DB_Medien;
use FWHist\Modules\Medien\API\MedienTableDefinitions;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
#use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;
# use FWHist\Services\API\AutoCompleteAPI;
# use FWHist\AllgVerw\API\BdldAutocompleteAPI;
# use FWHist\AllgVerw\API\StaatenAutocompleteAPI;

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$proj= isset($_GET['proj']) ? $_GET['proj'] : 'Z';

$md_id = (int) ($_POST['md_id'] ?? $_GET['md_id'] ?? 0);
$ei_id = (int) ($_POST['ei_id'] ?? $_GET['urh_id'] ?? 0);

$aufn_dat = isset($_GET['aufn_dat']) ? intval($_GET['aufn_dat'])  : 0;
$phase = (int) ($_POST['phase'] ?? 0);

$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canReadMandantData() || $ei_id < 1) {
    AccessGuard::deny('Anmeldung und eine gültige Eigentümer-ID sind erforderlich.');
}
$canEdit = $guard->canWriteMandantData('MEDIA', $ei_id);
if ($md_id === 0 && !$canEdit) {
    AccessGuard::deny('Zum Anlegen eines Medieneintrags ist Schreibrecht für diesen Eigentümer erforderlich.');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $phase === 1) {
    if (!$canEdit) {
        AccessGuard::deny('Änderungen sind nicht erlaubt.');
    }
    AccessGuard::requireCsrf();
}

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$pdo = $DBD->getPDO();

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$tabNam = $ei_id . '_dmedien';
$columnsByTables = $meta->getColumnsForTables([$tabNam ]);

$medien = new DB_Medien($DBD, $ei_id);

$mand = new DB_Mandanten($DBD);

$eigent_arr = $mand->getEigentuemerData($ei_id);
if ($eigent_arr === null) {
    AccessGuard::deny('Eigentümer nicht gefunden.', 404);
}

if ($md_id > 0) {
    $record = $medien->getMedienById($md_id);
    if ($record === null || ($record['md_dsn_1'] ?? '') === '0_Verz') {
        AccessGuard::deny('Medieneintrag nicht gefunden.', 404);
    }
}

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }
    
    if ($neu['md_aufn_datum'] == "") { 
        $Err_msg = 'Aufnamedatum fehlt oder kein Text eingegeben ';
        $phase = 0;
    } else {
        date_default_timezone_set('Europe/Berlin');
        
        $neu['md_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        
        $neu['md_changed_at'] = date('Y-m-d H:m:d' );
        $neu['md_Med_anz'] = intval($neu['md_Med_anz']);
        
        # var_dump($neu);
        
        foreach ( $neu as $key => $val) {
            if (substr($key,0,3) != 'ei_' && substr($key,0,3) != 'md_') {
                unset($neu[$key]);
            }
        }

        $neu['ei_id'] = $ei_id;
        $neu['md_id'] = $md_id;
        
        MedienTableDefinitions::ensureTenantTable($pdo, $ei_id, $tabNam);
        if ($neu['md_id'] == 0) { # neuengabe
            $ret = $medien->insertMedien($neu);
        } else { # Update
            $md_id = $neu['md_id'];
            $ret = $medien->updateMedien( $neu, ['md_id' => $md_id ] );
        }
        $ei_id = $neu['ei_id'];
        
        header("Location:  MedienDatenList.php?urh_id=$ei_id");
    }
     

}

$header = "";

$page = new HTMLPage('Medien- Detail- Verwaltung',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
        'jq' => false,
        'jqui' => false,
        'jq_accordion' => false,
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

// ============================================================================================================
// Eingabenerfassung und defa,uls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================

#var_dump($md_id);
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($md_id == 0) {
        # echo $forms->renderTrenner($eigent_arr['eig_name'] . ', ' .  $eigent_arr['eig_verant'] );
        $neu = array(
            'md_id' => 0,
            'ei_id' => "$ei_id",
            "md_Urheber" => $eigent_arr['eig_name'],
            "md_dsn_1" => "",
            'md_aufn_datum' => $aufn_dat,
            "md_beschreibg" => "",
            "md_namen" => "",
            "md_sammlg" => "",
            "md_feuerwehr" => "",
            "md_fw_id" => "",
            "md_media" => "Foto",
            'md_med_anz' => 0,
            'md_changed_id' => 0,
            'md_changed_at' => ""
        );
    } else {
        # var_dump($ei_id);
        $neu = $medien->getMedienById($md_id);
        
        #var_dump($neu);
        
    }
}


switch ($phase) {
    case 0:
        require ('MedDEdit_ph0_inc.php');
        break;
}
$page->renderFooter();
?>