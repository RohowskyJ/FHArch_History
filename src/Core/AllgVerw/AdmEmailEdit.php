<?php

/**
 * Automatische Benachrichtigung für ADMINS bei Änderungen, Wartung
 *
 * @author Josef Rohowsky - neu 2023
 *
 */
session_start();

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../";

// AUTOLOADER für Composer-Klassen laden
require $path2ROOT . 'vendor/autoload.php';

$module = 'ADM-Email';
$sub_mod = 'Edit';
$Zugr = 'ADM-ALL';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/admEmail_php-error.log.txt');

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

$ber = userBerechtigtOK($Zugr);

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Database\DB_AllgVerw;
use Fharch\Core\Services\TableColumnMetadata;

$debug = false;

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

#$TABUcss = true;
$header = "";
HTML_header('Admin- E-Mail -- Verwaltung', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$flow_list = False;
#var_dump($_POST);
#var_dump($_GET);
// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();

$pdo = $DBD->getPDO();

$dbGenericLog = DB_GenericLog::getInstance(); // oder new DB_GenericLog() je nach Implementierung
$emai = new DB_AllgVerw($dbGenericLog);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$columnsByTables = $meta->getColumnsForTables(['fv_adm_mail', 'fv_ben_dat' ]);
#var_dump($columnsByTables);
#var_dump($meta);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}

if (isset($_GET['ID'])) {
    $em_id = intval($_GET['ID']);
} else {
    $em_id = "";
}
if (isset($_POST['em_id'])) {
    $em_id = $_POST['em_id'];
}
if (isset($_GET['beId'])) {
    $be_ids = intval($_GET['beId']);
}
if (!isset($be_ids)) {
     $be_ids = 0;
}

if ($phase == 99) {
    header('Location: AdmEmailList.php');
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------

if ($phase == 0) {
    if ($em_id == 0) {
        $neu['em_id'] = 0;
        $neu['be_ids'] = 0;
        $neu['em_mail_grp'] = "";
        $neu['em_active'] = "Aktiv";
        $neu['em_aenddat'] = "";
        $neu['em_created_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['em_created_at'] = date('Y-m-d H:m:s');
        $neu['be_name'] = '';
        $neu['be_id'] = 0;
        $neu['be_email'] = "";
    } else {

        $neu_0 = $emai->getAdminMailByIdBE($em_id, $be_ids);
        
        $neu = $neu_0[0];
        
        $neu['be_name'] = '';
        $neu['be_id'] = 0;
        $neu['be_email'] = "";
        unset($neu_0);

    }
}


switch ($phase) {
 
    case 0:
        require 'AdmEmailEdit_ph0_inc.php';
        break;
    case 1:
        require 'AdmEmailEdit_ph1.inc.php';
}

HTML_trailer();

/**
 * Diese Funktion verändert die Zellen- Inhalte für die Anzeige in der Liste
 *
 * Funktion wird vom List_Funcs einmal pro Datensatz aufgerufen.
 * Die Felder die Funktioen auslösen sollen oder anders angezeigt werden sollen, werden hier entsprechend geändert
 *
 *
 * @param array $row
 * @param string $tabelle
 * @return boolean immer true
 *        
 * @global string $path2ROOT String zur root-Angleichung für relative Adressierung
 * @global string $T_List Auswahl der Listen- Art
 * @global string $module Modul-Name für $_SESSION[$module] - Parameter
 */
function modifyRow(array &$row, $tabelle)
{
    global $module, $path2ROOT, $T_List;

    # $defjahr = date("y"); // Beitragsjahr, ist Gegenwärtiges Jahr
    $em_id = $_SESSION[$module]['em_id'];
    $be_ids = $row['be_ids'];
    $row['be_ids'] = "<a href='AdmEmailEdit.php?ID=$em_id&be_ids=$be_ids&phase=0'>$be_ids</a>";

    return True;
} # Ende von Function modifyRow

?>