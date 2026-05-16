<!DOCTYPE html>
<?php
# session_start();
/**
 * Marktplatz- Liste
 * 
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
 * 
 * 
 */
session_start();

$module = 'OEF-AN';
$sub_mod = "LIST";
 
$Zugr = "ADM-OEF" ;
if ($_SESSION['BS_Prim']['Mod']['smod'] == 'index') {
    $Zugr = "Alle";
}

$tabelle = 'oe_termine';// <?php

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../../";

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/TE_LinkList_php-error.log.txt');
# var_dump($_SERVER);

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/Core/Services/FuncsLib.php';
require $path2ROOT . 'login/Core/Services/CommFuncsLib.php';
require $path2ROOT . 'login/Core/Services/ConstLib.php';

require $path2ROOT . 'vendor/autoload.php';

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Auth\Auth;

$ber = userBerechtigtOK($Zugr);
if (!$ber) {
    header("Location $path2ROOT/VFH/");
}

$header =   ""; 
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$ListHead = "Termine";
$title = "Termine";

$TABUcss = true;
HTML_header('Termine', $header, 'Admin', '80em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

$moduleId = $module."-".$sub_mod;
// Eigene Meldung mit Modulkennung loggen
# $logger->log('Starte Verarbeitung des Moduls', $moduleId, basename(__FILE__));

// XR_Database mit bestehender PDO-Instanz initialisieren
$DBD = new DB_GenericLog();
# var_dump($DBD);
$pdo = $DBD->getPDO();
# var_dump($pdo);

$flow_list = False;
$_SESSION[$module]['Return'] = False;

if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";

$list_ID = 'TE';

$lTitel = ["Alle" => "Alle Termine "];

$NeuRec = "";
if (isset($_SESSION['BS_Prim']['BE'])) {
    $NeuRec = " &nbsp; &nbsp; &nbsp; <a href='TerminEdit.php?ID=0' > Neuen Datensatz anlegen </a>";
} else {
    $lTitel = ["Alle." => "Alle Termine "];
}

require $path2ROOT . 'login/Core/Services/ListFuncsLib.php';

HTML_trailer();

?>