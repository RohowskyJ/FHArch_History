<?php

declare(strict_types=1);

/**
 * Unterstützer-  Wartung
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

// Projekt-/Session-Umgebung initialisieren.
$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
// Composer-Autoloader, bevor eigentliche Klassen verwendet werden.
require $systemBasePath . 'vendor/autoload.php';  //  __DIR__ == C:\JProgs\htdocs\Feuerwehr_Archiv\FWHist\login\Controllers; dirname(__DIR__, 2) == C:\JProgs\htdocs\Feuerwehr_Archiv\FWHist\

use FWHist\Database\DB_GenericLog;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;
use FWHist\Core\EnvSessionManager;
use FWHist\Auth\AccessGuard;
#new EnvSessionManager();

$module = 'ADM_Unterst';
$sub_mod = 'Edit';


// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/Unterstuetzer_fatal_error.log', $message, FILE_APPEND);
    }
});

$module = 'ADM-Unterst';
$sub_mod = "LIST";
    
$tabelle = 'mi_unterstuetzer';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/UnterstList_php-error.log.txt');
# var_dump($_SERVER);

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
$canEdit = $guard->canWriteMemberData();
if (!$canEdit) {
    http_response_code(403);
    exit('Für diesen Programmteil besteht keine Berechtigung.');
}
if ((int) ($_POST['phase'] ?? 0) === 1) {
    AccessGuard::requireCsrf();
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header =   "";
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$ListHead = "Unterstützer- Verwaltung - Administrator ";
$title = "Unterstützer Daten";

$page = new HTMLPage('Unterstützer- Verwaltung',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

$debug = False; // Debug output Ein/Aus Schalter

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
#var_dump($meta);

$columnsByTables = $meta->getColumnsForTables(['fv_unterst' ]);
#var_dump($columnsByTables);
# var_dump($meta);
$mitgl = new MI_MitgliederModule($DBD);
#var_dump($mitgl);
#var_dump($_SERVER);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $fu_id = intval($_GET['ID']);
} else {
    $fu_id = 0;
}
if (isset($_POST['fu_id'])) {
    $fu_id = intval($_POST['fu_id']);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (int) ($_POST['phase'] ?? 0) === 1 && !$canEdit) {
    http_response_code(403);
    exit('Änderungen sind nicht erlaubt.');
}

if ($phase == 99) {
    header('Location: UnterstList.php');
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($fu_id == 0) {
        $neu['fu_id'] = $fu_id;
        $neu['fu_aktiv'] = "J";
        $neu['fu_kateg'] = "FF";
        $neu['fu_weihn_post'] = "J";
        $neu['fu_zugr'] = "N";
        $neu['fu_tit_vor'] = "";
        $neu['fu_tit_nach'] = "";
        $neu['fu_anrede'] = "Hr.";
        $neu['fu_name'] = $neu['fu_vname'] = "";
        $neu['fu_dgr'] = $neu['fu_plz'] = $neu['fu_ort'] = $neu['fu_adresse'] = "";
        $neu['fu_tel'] = $neu['fu_email'] = $neu['fu_orgname'] = "";
        $neu['fu_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['fu_changed_at'] = date('Y-m-d H:m:s');
    } else {

        $neu_0 = $mitgl->getUnterstById($fu_id);
        
        $neu = $neu_0[0];
        
        #var_dump($neu);
  
    }
}

if ($phase == 1) {

    foreach ($_POST as $name => $value) {
        $neu[$name] = trim($value);
    }
    #var_dump($neu);
}

switch ($phase) {
    case 0:
        require 'UnterstEdit_ph0.inc.php';
        break;
    case 1:
        require "UnterstEdit_ph1.inc.php";
        break;
}
$page->renderFooter();
?>