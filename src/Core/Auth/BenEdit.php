<?php
/**
 * Benutzer- verwaltung, Wartung
 * 
 * @author Josef Rohowsky - neu 2020
 */
session_start();

$module = 'ADM-Benu';
$sub_mod = 'Edit';

$Zugr = "ADM-ALL";

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/BenEdit_php-error.log.txt');


/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../";

$debug = False; // Debug output Ein/Aus Schalter

// AUTOLOADER für Composer-Klassen laden
require $path2ROOT . 'vendor/autoload.php';

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

$ber = userBerechtigtOK($Zugr);
# var_dump($ber);
if (!$ber) {exit;}

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Database\DB_Benutzer;
use Fharch\Core\Services\TableColumnMetadata;
use Fharch\Core\Services\FormRendererFlex;

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

$TABUcss = true;
$header = "";
HTML_header('Benutzer- Verwaltung', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();

$pdo = $DBD->getPDO();

$dbGenericLog = DB_GenericLog::getInstance(); // oder new DB_GenericLog() je nach Implementierung
$benu = new DB_Benutzer($dbGenericLog);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$columnsByTables = $meta->getColumnsForTables(['fv_benutzer', 'fv_ben_dat' ]); // , 'fv_mand_erl', 'fv_rolle', 'fv_rollen_beschr'

// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $fd_id = $_GET['ID'];
} else {
    $fd_id = "";
}
if (isset($_POST['fd_id'])) {
    $fd_id = $_POST['fd_id'];
}

if ($phase == 99) {
    header('Location: VS_BenList.php');
}

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
        $neu['fd_changed_at'] = date('Y-m-d H:m:s');
        $neu['be_id'] = 0;
        $neu['be_mi_id'] = 0;
    } else {

        $neu = $benu->getUserDataById((string)$fd_id);
        
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

    foreach ($_POST as $name => $value) {
        $neu[$name] = trim($value);
    }
}

switch ($phase) {
    case 0:
        require 'BenEdit_ph0_inc.php';
        break;
    case 1:
        require "BenEdit_ph1_inc.php";
        break;
}
HTML_trailer();
?>