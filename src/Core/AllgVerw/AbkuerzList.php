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

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/Abk_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'ADM-Abk';
$sub_mod = "LIST";

$Zugr = "ADM-AB";

$tabelle = '';// <?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/AbkuerzList_php-error.log.txt');
# var_dump($_SERVER);

/**
 * Angleichung an den Root-Path
 *
 * @var string $$path2ROOT
 */
$path2ROOT = "../../../";

// AUTOLOADER für Composer-Klassen laden
require $path2ROOT . 'vendor/autoload.php';

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

$ber = userBerechtigtOK($Zugr);

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

use Fharch\Core\Database\DB_GenericLog;

$header =   ""; 
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$ListHead = "Abkürzungen- Verwaltung - Administrator ";
$title = "Abkürzungen- Daten";

# $TABU = true;
$TABUcss = true;
HTML_header('Abkürzungen- Verwaltung', $header, 'Admin', '200em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

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
    header("Location: /src/Core/Controller/MainMenu.php");
}

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";
$list_ID = 'ABK';
$lTitel = ["Alle" =>"Alle Abkürzungen"
    ,'MA_F' => 'Abk. Motorisierte Fahrzeuge'
    ,'MA_G' => 'Abk. Motorisierte Geräte'
    ,'MU_F' => 'Abk. Muskelgezogene Fahrzeuge'
    ,'MU_G' => 'Abk. Muskelbetriebene Geräte'
    ,'ORG'  => 'Abk. Organisation '];

$NeuRec = "<a href='AbkuerzEdit.php?ID=0' >Neue Abkürzung eingeben</a>"; 

require $path2ROOT . 'src/Core/Services/ListFuncsLib.php';

HTML_trailer();

?>
