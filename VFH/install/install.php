<?php

/**
 * Installations und Configurations- Durchführung
 *  neu J.Rohowsky 2025
 * 
 * ist install.ini vorhanden?  JA -> Daten und Installation überprüfen, Tabellen in Datenbank anlegen (if not exist !), anlegen der /login/common/config_?.ini
 *                             Nein -> Daten abfragen, als Muster install_ori.ini nutzen, prüfen ob DB zugriff, Ja -> /login/common/config_?.ini erstellen, Tabellen erstellen (if not exists)
 * 
 */
session_start();
$module = "install";

$path2ROOT = "../../";

$debug = False; // Debug output Ein/Aus Schalter

if (! isset($actor) || $actor == "") {
    $actor = $_SERVER["PHP_SELF"];
}

require $path2ROOT . 'login/common/BA_Funcs.lib.php';
require $path2ROOT . 'login/common/BA_Edit_Funcs.inclib.php';
require $path2ROOT . 'login/common/BA_Tabellen_Spalten.lib.php';
require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';
require $path2ROOT . 'login/common/VF_Const.lib.php';

initial_debug();

echo "<!DOCTYPE html>";
echo "<html lang='de'>"; # style='overflow-x:scroll;'
echo "<head>";
echo "  <meta charset='UTF-8'>";
echo "  <title>Installation und Konfiguration Feuerwehr_Archiv</title>";
echo "  <meta  name='viewport' content='width=device-width, initial-scale=1.00'>";
echo "  <link  href='" . $path2ROOT . "login/common/css/w3.css'     rel='stylesheet' type='text/css'>";
echo "  <link  href='" . $path2ROOT . "login/common/css/add.css' rel='stylesheet' type='text/css'>";
# echo " <script src='".$path2ROOT."login/common/javascript/stoprkey.js' type='text/javascript'></script>";
echo '<meta name="description" content="Feuerwehrhistoriker Dokumentationen - Archiv, Inventar, Beschreibungen, Kataloge, ...">';
echo "<meta name='copyright' content='FT Ing. Josef Rohowsky 2020-2025'>";
echo '<meta name="robots" content="noindex">';
echo '<meta name="robots" content="nofollow">';
echo "</head>";

echo "<body class='w3-container' style='max-width:75em;' >";

echo "<form id='myform' name='myform' method='post' action='$actor' enctype='multipart/form-data'>";
echo "<fieldset>";

if (isset($_SESSION[$module]['inst'])) {
    echo "<fieldset>";
    echo $_SESSION[$module]['inst'];
    if (isset($_SESSION[$module]['vreg'])) {
        echo "<span style='float:right'>Vereins-Reg. Zahl ".$_SESSION[$module]['vreg']."</span><br>";
    }
    echo "<b>Erfassen der Parameter für die Installation</b>";
    if (isset($_SESSION[$module]['sign'])) {
        $pict = "<img src='".$path2ROOT."login/common/imgs/".$_SESSION[$module]['sign']."' alt='Logo' width='70px'>";
        echo "<span style='float:right'> ".$_SESSION[$module]['sign']." $pict </span>";
    }
    
    echo "</fieldset>";
}
/**
 * Installationsabfragen
 * phase = 1 Datenbank- Parameter
 * Phase = 3 Anzeige. Paramerter
 * Phase = 5 Aktive Module
 * die geraden Phasen ist die Speicherung der vorhergehenden Eingaben
 */

$phase = 1;
if (isset($_GET['phase'])) {
    $phase = $_GET['phase'];
}

if (isset($_POST['phase']))  {
    $phase = $_POST['phase'];
}
if (!isset($_SESSION['VF_Prim'])) {
    $_SESSION['VF_Prim']['p_uid'] = !'9999999';
}

if ($phase == 1) { // Eingabe der Datenbank- Parameter
    if (is_file($path2ROOT . 'login/common/config_d.ini')) {
        $ini_arr = parse_ini_file($path2ROOT.'login/common/config_d.ini',True,INI_SCANNER_NORMAL);
        
        $l_dbh = $ini_arr["localhost"]['l_dbh'];
        $l_dbu = $ini_arr["localhost"]['l_dbu'];
        $l_dbp = $ini_arr["localhost"]['l_dbp'];
        $l_dbn = $ini_arr["localhost"]['l_dbn'];

        $h_dbh = $ini_arr["HOST"]['h_dbh'];
        $h_dbu = $ini_arr["HOST"]['h_dbu'];
        $h_dbp = $ini_arr["HOST"]['h_dbp'];
        $h_dbn = $ini_arr["HOST"]['h_dbn'];
        

    } else {
        
        $l_dbh = 'localhost';
        $l_dbu = '';
        $l_dbp = '';
        $l_dbn = '';
        $h_dbh = 'hostname:port';
        $h_dbu = '';
        $h_dbp = '';
        $h_dbn = '';
    }
    require_once "install_1.inc.php";
}
 
if ($phase == 2) { // sichern der Datenbank- Parameter  
    require_once "install_2.inc.php";
}

if ($phase == 3 ) {
    
    require_once "install_3.inc.php"; // Datenbanken erstellen
}

if ($phase == 4) { // Eingabe der Anzeige. Daten
     header ("location: ".$path2ROOT."login/common/Proj_Conf_Edit.php?inst=J");
}

echo '</fieldset>';
echo "</form>";
echo "</div></body></html>";

?>