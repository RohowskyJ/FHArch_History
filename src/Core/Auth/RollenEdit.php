<?php

/**
 * Rollen- für Benutzer  zuordnen, verwaltung, Wartung
 *
 * @author Josef Rohowsky - neu 2020
 */
session_start();
echo "<!doctype html>"; 
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
    
$module = 'ADM-Rolle';
$sub_mod = 'Edit';

$Zugr = "ADM-ALL";

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = '../../../';

// AUTOLOADER für Composer-Klassen laden
require $path2ROOT . 'vendor/autoload.php';

/*
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/bootstrap_php-error.log.txt');
*/

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

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';
# require $path2ROOT . 'login/common/VF_Const.lib.php';

$TABUcss = true;
$header = "";
HTML_header('Rollen- Zuordnung  für Benutzer- Verwaltung', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

// ============================================================================================================
// Eingabenerfassung und defauls
// =================================

$DBD = new DB_GenericLog();

$pdo = $DBD->getPDO();

$dbGenericLog = DB_GenericLog::getInstance(); // oder new DB_GenericLog() je nach Implementierung
$benu = new DB_Benutzer($dbGenericLog);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$columnsByTables = $meta->getColumnsForTables(['fv_rolle', 'fv_rollen_beschr']);

// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $fr_id = (intval($_GET['ID']));
} else {
    $fr_id = 0;
}
$benName = $_GET['benu'] ?? '';
if (isset($_POST['benu'] )) {
    $benName = $_POST['benu'] ;
}

if (isset($_POST['fr_id'])) {
    $fr_id = (intval($_POST['fr_id']));
}
if (isset($_GET['beId'])) {
    $be_id = (intval($_GET['beId']));
} elseif (isset($_POST['be_id'])) {
    $be_id = (intval($_POST['be_id'])); 
}
    
if ($phase == 99) {
    header('Location: BenEdit.php?id=be_id');
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($fr_id == 0) {
        $neu['fr_id'] = $fr_id;
        $neu['be_id'] = $be_id;
        $neu['fl_id'] = $neu['fr_aktiv'] = "";
        $neu['fr_created_id'] = $neu['fr_changed_id'] = 0;
        $neu['fr_created_at'] = $neu['fr_changed_at'] = "0000-00-00 00:00:00";
        $neu['descript'] = "";
        $neu['Rolle'] = "";
    } else {
        
        $neu = $benu->getRoleById($fr_id, $be_id);
        $neu['descript'] =  "";
        $neu['Rolle'] = "";
        # var_dump($neu);
        
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
        require 'RollenEdit_ph0_inc.php';
        break;
    case 1:
        require "RollenEdit_ph1_inc.php";
        break;
}
HTML_trailer();
?>
