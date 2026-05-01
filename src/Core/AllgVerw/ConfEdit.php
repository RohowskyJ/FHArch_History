<!DOCTYPE html>
<?php

/**
 * E-Mail Admin- Verständigen Zuordnung
 *
 * @author Josef Rohowsky - neu 2023
 *
 *
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
    
$module = 'ADM-Conf';
$sub_mod = 'all';

$Zugr = 'ADM-ALLE';

$tabelle = 'fv_proj_config';

const Prefix = '';

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../";

// AUTOLOADER für Composer-Klassen laden
require $path2ROOT . 'vendor/autoload.php';

$debug = false; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Database\DB_AllgVerw;
use Fharch\Core\Services\TableColumnMetadata;
use Fharch\Core\Services\FormRendererFlex;

/* Für Installation: Zugriffberechtigung aushebenln : im Inst-Script $_SESS setzen */
$ber = userBerechtigtOK($Zugr);
if (!$ber) {
    header("Location $path2ROOT/public/");
}

console_log('proj start');

$jq = $jqui = true;
$BA_AJA = true;

HTML_header('Konfigurations- Verwaltung', '', 'Form', '70em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

initial_debug('GET','POST');

if (isset($_GET['inst'])) {
    if ($_GET['inst'] === "J") {
        $_SESSION[$module]['inst'] =  $path2ROOT."VFH/install/install_cleanup.php";
    }
}

// =====================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();

$pdo = $DBD->getPDO();

$dbGenericLog = DB_GenericLog::getInstance(); // oder new DB_GenericLog() je nach Implementierung
$conf = new DB_AllgVerw($dbGenericLog);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$columnsByTables = $meta->getColumnsForTables(['fv_proj_config']);

// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}

$srv = $_SERVER['HTTP_HOST'];
//$caller = $_SERVER['REQUEST_URI'];
//$cal_arr = explode("/",$caller); // wird für css auswahl verwendet
$cfg = 'config_s_l.ini';
if (mb_strtolower($srv) === "feuerwehrhistoriker.at" || mb_strtolower($srv) === "www.feuerwehrhistoriker.at") {
    $cfg = "config_s_vfh.ini";
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {


    if (is_file($path2ROOT . 'config/'. $cfg)) {
        $ini_arr = parse_ini_file($path2ROOT.'config/'.$cfg, true, INI_SCANNER_NORMAL);

        $neu['c_Institution'] = $ini_arr["Config"]['inst'];
        $neu['c_Vereinsreg'] = $ini_arr["Config"]['vreg'];
        $neu['c_Eignr'] = $ini_arr["Config"]['eignr'];
        $neu['c_Verantwortl'] = $ini_arr["Config"]['vant'];
        $neu['c_email'] = $ini_arr["Config"]['vema'];
        $neu['c_Ver_Tel'] = $ini_arr["Config"]['vtel'];
        $neu['c_mode'] = $ini_arr["Config"]['mode'];
        $neu['c_Wartung'] = $ini_arr["Config"]['wart'];
        $neu['c_Wart_Grund'] = $ini_arr["Config"]['warg'];
        $neu['c_bild_1'] = $ini_arr["Config"]['sign'];
        $neu['c_bild_2'] = $ini_arr["Config"]['fpage'];
        $neu['c_Homepage'] = $ini_arr["Config"]['homp'];
        $neu['c_ptyp']  = $ini_arr["Config"]['homp'];
        $neu['c_store']  = $ini_arr["Config"]['store'];
        $neu['c_def_pw']  = $ini_arr["Config"]['def_pw'];
        $neu['c_Perr']  = $ini_arr["Config"]['cPerr'];
        $neu['c_Debug'] = $ini_arr["Config"]['cDeb'];
        $neu['c_bpath'] = $ini_arr["Config"]['bpath'];
        $neu['c_miBeitr'] = $ini_arr["Config"]['miBeitr'];
           
    } else {
        $neu['c_Incstitution'] = "Organisations- Bezeichnung";
        $neu['c_Vereinsreg'] = 'Vereinsreg- Nummer';
        $neu['c_Eignr'] = 'Eigentümer- Nummer';
        $neu['c_Verantwortl'] = 'Name des Verantwortlichen';
        $neu['c_email'] = 'email@verantwortl.cc';
        $neu['c_Ver_Tel'] = '+43 - Tel-Nr des Verantwortlichen';
        $neu['c_mode'] = 'Single';
        $neu['c_Wartung'] = 'N';
        $neu['c_Wart_Grund'] = "";
        $neu['c_bild_1'] = 'Signet.jpg';
        $neu['c_bild_2'] = 'Bild_1_Seite.png';
        $neu['c_Homepage'] = 'https://www.homepage-Name.at';
        $neu['c_ptyp']  = "";
        $neu['c_store']  = "AOrd_Verz";
        $neu['c_def_pw']  = "defaultPW";
        $neu['c_Perr']  = 'error_log.txt';
        $neu['c_Debug'] = 'debug_log.txt';
        $neu['c_bpath'] = 'FSArch-Hist';
        $neu['c_miBeitr'] = '25';
    }
    
}

if ($phase == 1) {

    foreach ($_POST as $name => $value) {
        $neu[$name] = trim($value);
    }
    
}


switch ($phase) {
    case 0:
        require('ConfEdit_ph0_inc.php');
        break;
    case 1:
        require "ConfEdit_ph1_inc.php";
        break;
}
HTML_trailer();
?>