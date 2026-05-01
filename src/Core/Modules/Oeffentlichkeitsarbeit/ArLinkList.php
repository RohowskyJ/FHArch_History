<!DOCTYPE html>
<?php
# session_start();
/**
 * Mitglieder Verwaltung Liste
 * 
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
 * 
 * 
 */
session_start();

$module = 'OEF-AR';
$sub_mod = "LIST";
 
$Zugr = "Alle";

$tabelle = 'fv_falink';// <?php

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../../";

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/Ar_LinkList_php-error.log.txt');
# var_dump($_SERVER);

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

require $path2ROOT . 'vendor/autoload.php';

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Auth\Auth;

$ber = userBerechtigtOK($Zugr);
if (!$ber) {
    header("Location $path2ROOT/public/");
}

$header =   ""; 
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$ListHead = "Links zu Öffentl. Bibliotheken und Archiven";
$title = "Öffentl. Bibliotheken";

$TABUcss = true;
HTML_header('Biblitheks- und Archiv- Links', $header, 'Admin', '80em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

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
if ($phase == 99) {
    header("Location: /login/FS_C_Menu.php");
}

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";
$list_ID = 'AR';
$lTitel = ["Alle" => "Alle verfügbaren LINKS "];
if ($_SESSION['BS_Prim']['Mod']['smod'] == 'ExtStart') {
    $lTitel = ["Extern" => "Alle verfügbaren LINKS "];
}

$NeuRec = "";
if ($_SESSION['BS_Prim']['Mod'] == 'IntStart') {
    $NeuRec = " &nbsp; &nbsp; &nbsp; <a href='Vs_O_AR_Edit.php?ID=0' > Neuen Datensatz anlegen </a>";
}

require $path2ROOT . 'src/Core/Services/ListFuncsLib.php';

HTML_trailer();

?>