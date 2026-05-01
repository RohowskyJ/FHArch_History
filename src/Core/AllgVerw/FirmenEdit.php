<?php

/**
 * Firmen- verwaltung, Wartung
 * 
 * @author Josef Rohowsky - neu 2020
 */
session_start();

$module = 'ADM-Firmen';
$sub_mod = 'Edit';
$Zugr = 'ADM-FI';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/bootstrap_php-error.log.txt');

$debug = False; // Debug output Ein/Aus Schalter

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../";

/** AUTOLOADER für Composer-Klassen laden */
require $path2ROOT . 'vendor/autoload.php';

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

$ber = userBerechtigtOK($Zugr);

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Database\DB_AllgVerw;
use Fharch\Core\Services\TableColumnMetadata;

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

$TABUcss = true;
$header = "";
HTML_header('Firmen- Verwaltung', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();

$pdo = $DBD->getPDO();

$dbGenericLog = DB_GenericLog::getInstance(); // oder new DB_GenericLog() je nach Implementierung
$firm = new DB_AllgVerw($dbGenericLog);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$columnsByTables = $meta->getColumnsForTables(['fv_firmen']);

// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $fi_id = $_GET['ID'];
} else {
    $fi_id = "0";
}
if (isset($_POST['fi_id'])) {
    $fi_id = $_POST['fi_id'];
}

if ($phase == 99) {
    header('Location: FirmenList.php');
}
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($fi_id == 0) {
        $neu['fi_id'] = $fi_id;
        $neu['fi_abk'] = $neu['fi_name'] = $neu['fi_ort'] = $neu['fi_vorgaenger'] = "";
        $neu['fi_funkt'] = $neu['fi_inet'] = "";
        $neu['fi_changed_id_s'] = "";
        $neu['fi_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['fi_chanded_at'] = date('Y-m-d H:m:s');
    } else {

        $neu_0 = $firm->getFirmenById($fi_id);
  
        $neu = $neu_0[0];
        unset($neu[0]);
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

    foreach ($_POST as $name => $value) {
        $neu[$name] = trim($value);
    }
}

switch ($phase) {
    case 0:
        require 'FirmenEdit_ph0_inc.php';
        break;
    case 1:
        require "FirmenEdit_ph1_inc.php";
        break;
}
HTML_trailer();
?>