<?php

declare(strict_types=1);

/**
 * Firmen- verwaltung, Wartung
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
new EnvSessionManager();

$module = 'ADM-Abk';
$sub_mod = 'Edit';

$Zugr = "ADM-AB";

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/Abk_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . 'Abk_Edit_php-error.log.txt');

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

use FWHist\Database\DB_GenericLog;
use FWHist\Database\DB_AllgVerw;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

$canEdit = !empty($_SESSION['BS_Prim']['BE']['be_id']) && userHasRole($Zugr);
if (!$canEdit) {
    http_response_code(403);
    exit('Für diesen Programmteil besteht keine Berechtigung.');
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('Abkürzungen- Verwaltung',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
        'jq' => true,
        'jqui' => true,
        'jq_accordion' => true,
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

// XR_Database mit bestehender PDO-Instanz initialisieren
$DBD = new DB_GenericLog();

$pdo = $DBD->getPDO();

$dbGenericLog = DB_GenericLog::getInstance(); // oder new DB_GenericLog() je nach Implementierung
$abku = new DB_AllgVerw($dbGenericLog);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$columnsByTables = $meta->getColumnsForTables(['fv_abk']);

// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $ab_id = intval($_GET['ID']);
} else {
    $ab_id = 0;
}
if (isset($_POST['ab_id'])) {
    $ab_id = intval($_POST['ab_id']);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (int) ($_POST['phase'] ?? 0) === 1 && !$canEdit) {
    http_response_code(403);
    exit('Änderungen sind nicht erlaubt.');
}

if ($phase == 99) {
    header('Location: AbkuerzList.php');
}
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($ab_id == 0) {
        if (!$canEdit) {
            http_response_code(403);
            exit('Eine Neuanlage ist nicht erlaubt.');
        }
        $neu['ab_id'] = $ab_id;
        $neu['ab_grp'] = $neu['ab_abk'] = $neu['ab_bezeichn'] = $neu['ab_gruppe'] = "";
        $neu['ab_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['ab_chanded_at'] = date('Y-m-d H:m:s');
        
        var_dump($neu);
    } else {

        $neu_0 = $abku->getAbkuerzById($ab_id);
        
        $neu = $neu_0[0];
        # var_dump($neu);
   
    }
}

if ($phase == 1) {

    foreach ($_POST as $name => $value) {
        $neu[$name] = trim($value);
    }
}

switch ($phase) {
    case 0:
        require 'AbkuerzEdit_ph0_inc.php';
        break;
    case 1:
        require "AbkuerzEdit_ph1_inc.php";
        break;
}
$page->renderFooter();
?>