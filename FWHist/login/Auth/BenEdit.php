<?php

declare(strict_types=1);

/**
 * Benutzer- verwaltung, Wartung
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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_Benutzer;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

$module = 'ADM-Benu';
$sub_mod = 'Edit';

# $Zugr = "ADM-ALLE";

$debug = False; // Debug output Ein/Aus Schalter

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() use ($systemBasePath) {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents($systemBasePath . 'login/AOrd_Verz/Ben_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $systemBasePath . 'login/AOrd_Verz/Ben_Edit_php-error.log.txt');

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

use FWHist\Database\DB_GenericLog;
use FWHist\Auth\AccessGuard;

// RBAC: Benutzerverwaltung nur für ADM-ZUGR (und ADM-ALLE) - VOR jeder Ausgabe
$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canManageAccess()) {
    AccessGuard::deny('Für die Benutzerverwaltung ist die Berechtigung ADM-ZUGR erforderlich.');
}
if ((int) ($_POST['phase'] ?? 0) === 1) {
    AccessGuard::requireCsrf();   // Token aus dem Formular (BenEdit_ph0_inc.php)
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('Benutzer- Verwaltung',
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

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$pdo = $DBD->getPDO();

$dbGenericLog = DB_GenericLog::getInstance(); // oder new DB_GenericLog() je nach Implementierung
$benu = new DB_Benutzer($dbGenericLog);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$columnsByTables = $meta->getColumnsForTables(['fv_benutzer', 'fv_ben_dat', 'fv_benutzer_mandant' ]); 

// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
$fd_id = (int) ($_POST['fd_id'] ?? $_GET['ID'] ?? 0);   // Profil-Nr (fv_ben_dat), 0 = neu

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($fd_id == 0) {
        $neu['fd_id'] = $fd_id;
        $neu['fd_anrede'] = "Hr.";
        $neu['fd_tit_vor'] = $neu['fd_name'] = $neu['fd_vname'] = $neu['mi_tit_nach'] = "";
        $neu['fd_adresse'] =  $neu['fd_plz'] = $neu['fd_ort'] = "";
        $neu['fd_staat_abk'] = "AT";
        $neu['staat'] = 'Österreich';
        $neu['fd_tel'] = $neu['fd_email'] = $neu['fd_email_status'] = $neu['fd_hp'] = "";
        $neu['fd_geb_dat'] = $neu['fd_sterb_dat'] = $neu['fd_austr_dat'] = "0000-00-00";
        $neu['fd_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['fd_changed_at'] = date('Y-m-d H:i:s');
        $neu['be_id'] = 0;
        $neu['be_mi_id'] = 0;
    } else {

        $neu = $benu->getUserDataById((string)$fd_id);
        if ($neu === null) {
            AccessGuard::deny('Benutzer nicht gefunden.', 404);
        }
        
        $neu['staat_id'] = ''; 
        $neu['staat'] = ""; //Auslesen!
        
        #var_dump($neu);
        
        if ($debug) {
            echo '<pre class=debug>';
            echo '<hr>$neu: ';
            var_dump($neu);
            echo '</pre>';
        }
    }
}

if ($phase == 1) {
    // Nur diese Felder werden aus dem Formular übernommen (kein Durchreichen beliebiger POST-Felder)
    $allowedFields = [
        'fd_anrede', 'fd_tit_vor', 'fd_vname', 'fd_name', 'fd_tit_nach', 'fd_adresse', 'fd_plz', 'fd_ort',
        'fd_staat_abk', 'staat_id', 'fd_tel', 'fd_email', 'fd_email_status', 'fd_hp',
        'fd_geb_dat', 'fd_sterb_dat', 'fd_austr_dat', 'be_mi_id',
    ];
    $neu = [];
    foreach ($allowedFields as $field) {
        $value = $_POST[$field] ?? '';
        $neu[$field] = is_string($value) ? trim($value) : '';
    }
    $neu['fd_id'] = $fd_id;
    $neu['be_id'] = (int) ($_POST['be_id'] ?? 0);   // nur zur Anzeige; wird beim Speichern nicht geändert
}

switch ($phase) {
    case 0:
        require 'BenEdit_ph0_inc.php';
        break;
    case 1:
        require "BenEdit_ph1_inc.php";
        break;
}
$page->renderFooter();
?>