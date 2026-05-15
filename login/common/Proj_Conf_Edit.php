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
    
$module = 'Proj_Conf';
$sub_mod = 'all';

$tabelle = 'fh_proj_config';

const Prefix = '';

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../";

/**
 * Includes-Liste
 * enthält alle jeweils includierten Scritpt Files
 */
$_SESSION[$module]['Inc_Arr']  = array();
$_SESSION[$module]['Inc_Arr'][] = "Proj_Conf_Edit.php";

$_SESSION[$module]['all_upd'] = '1';
 
$debug = false; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/common/BA_HTML_Funcs.lib.php';
require $path2ROOT . 'login/common/BA_Funcs.lib.php';
require $path2ROOT . 'login/common/BA_Edit_Funcs.lib.php';
require $path2ROOT . 'login/common/BA_Tabellen_Spalten.lib.php';
require $path2ROOT . 'login/common/VF_Const.lib.php';
require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

console_log('proj start');

$jq = $jqui = true;
$BA_AJA = true;

BA_HTML_header('Konfigurations- Verwaltung', '', 'Form', '70em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

initial_debug();

if (isset($_GET['inst'])) {
    if ($_GET['inst'] === "J") {
        $_SESSION[$module]['inst'] =  $path2ROOT."VFH/install/install_cleanup.php";
    }
}

// =====================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================
$LinkDB_Database = "";
$db = LinkDB('VFH');

// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}

$Edit_Funcs_FeldName = false; // Feldname der Tabelle wird nicht angezeigt !!

Tabellen_Spalten_parms($db, 'fh_proj_config');

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {


    if (is_file($path2ROOT . 'login/common/config_s.ini')) {
        $ini_arr = parse_ini_file($path2ROOT.'login/common/config_s.ini', true, INI_SCANNER_NORMAL);

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
    } else {
        $neu['c_Institution'] = "Organisations- Bezeichnung";
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
    }
    if (is_file($path2ROOT . 'login/common/config_m.ini')) {
        $ini_arr = parse_ini_file($path2ROOT.'login/common/config_m.ini', true, INI_SCANNER_NORMAL);

        $neu['c_Module_1'] = $ini_arr["Modules"]['m_1'];
        $neu['c_Module_2'] = $ini_arr["Modules"]['m_2'];
        $neu['c_Module_3'] = $ini_arr["Modules"]['m_3'];
        $neu['c_Module_4'] = $ini_arr["Modules"]['m_4'];
        $neu['c_Module_5'] = $ini_arr["Modules"]['m_5'];
        $neu['c_Module_6'] = $ini_arr["Modules"]['m_6'];
        $neu['c_Module_7'] = $ini_arr["Modules"]['m_7'];
        $neu['c_Module_8'] = $ini_arr["Modules"]['m_8'];
        $neu['c_Module_9'] = $ini_arr["Modules"]['m_9'];
        $neu['c_Module_10'] = $ini_arr["Modules"]['m_10'];
        $neu['c_Module_11'] = $ini_arr["Modules"]['m_11'];
        $neu['c_Module_12'] = $ini_arr["Modules"]['m_12'];
        $neu['c_Module_13'] = $ini_arr["Modules"]['m_13'];
        $neu['c_Module_14'] = $ini_arr["Modules"]['m_14'];
        $neu['c_Module_15'] = $ini_arr["Modules"]['m_15'];
    } else {
        $neu['c_Module_1'] = 'J';
        $neu['c_Module_2'] = 'J';
        $neu['c_Module_3'] = 'J';
        $neu['c_Module_4'] = 'J';
        $neu['c_Module_5'] = 'J';
        $neu['c_Module_6'] = 'J';
        $neu['c_Module_7'] = 'J';
        $neu['c_Module_8'] = 'J';
        $neu['c_Module_9'] = 'J';
        $neu['c_Module_10'] = 'J';
        $neu['c_Module_11'] = 'J';
        $neu['c_Module_12'] = 'J';
        $neu['c_Module_13'] = 'J';
        $neu['c_Module_14'] = 'J';
        $neu['c_Module_15'] = 'J';
    }

    if ($debug) {
        echo '<pre class=debug>';
        echo '<hr>\$neu: ';
        print_r($neu);
        echo '</pre>';
    }
}

if ($phase == 1) {

    foreach ($_POST as $name => $value) {
        $neu[$name] = trim(mysqli_real_escape_string($db, $value));
    }
    
    exit;
}


switch ($phase) {
    case 0:
        require('Proj_Conf_Edit_ph0.inc.php');
        break;
    case 1:
        require "Proj_Conf_Edit_ph1.inc.php";
        break;
}
BA_HTML_trailer();
?>