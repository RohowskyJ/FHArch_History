<?php

/**
 * Firmen- verwaltung, Wartung
 * 
 * @author Josef Rohowsky - neu 2020
 */
session_start();

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'ADM-Abk';
$sub_mod = 'Edit';

$Zugr = "ADM-AB";

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */

$path2ROOT = '../../../';

// AUTOLOADER für Composer-Klassen laden
require $path2ROOT . 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/AB_kuerz_php-error.log.txt');

$debug = false; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

$ber = userBerechtigtOK($Zugr);
if (!$ber) {
    header("Location $path2ROOT/public/");
}


require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Database\DB_AllgVerw;
use Fharch\Core\Services\TableColumnMetadata;

$TABUcss = true;
$header = "";
HTML_header('Abkürzungen- Verwaltung', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

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

if ($phase == 99) {
    header('Location: AbkuerzList.php');
}
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($ab_id == 0) {
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
HTML_trailer();
?>