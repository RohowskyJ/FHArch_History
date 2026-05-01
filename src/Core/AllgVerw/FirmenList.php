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
        file_put_contents(__DIR__ . '/fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'ADM-Firmen';
$sub_mod = "LIST";
$Zugr = 'ADM-FI';

$tabelle = 'fv_firmen';// <?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/VS_Firmen_php-error.log.txt');
# var_dump($_SERVER);

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../";

// AUTOLOADER für Composer-Klassen laden
require $path2ROOT . 'vendor/autoload.php';

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';
require $path2ROOT . 'login/Basis/common/BS_Logger_CLS.php';

$ber = userBerechtigtOK($Zugr);

use FhArch\Core\Database\DB_GenericLog;
require $path2ROOT . "src/Core/Database/DB_GenericLog.php";

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

// XR_Database mit bestehender PDO-Instanz initialisieren
$DBD = new DB_GenericLog();

$pdo = $DBD->getPDO();

$header =   ""; 
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$ListHead = "Firmen- Verwaltung - Administrator ";
$title = "Firmen- Daten";

# $TABU = true;
$TABUcss = true;
HTML_header('Firmen- Verwaltung', $header, 'Admin', '200em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

$moduleId = $module."-".$sub_mod;
// Eigene Meldung mit Modulkennung loggen
# $logger->log('Starte Verarbeitung des Moduls', $moduleId, basename(__FILE__));

// XR_Database mit bestehender PDO-Instanz initialisieren
$DBD = new DB_GenericLog();

$pdo = $DBD->getPDO();


$flow_list = False;
$_SESSION[$module]['Return'] = False;

if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if ($phase == 99) {
    header("Location: /src/Core/Controllers/MainMenu.php");
}

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";
$list_ID = 'FIR';
$lTitel = ["Alle" => "Alle Firmen", 
    "FZGE" => "Alle Fahrzeug- und Geräte- Hersteller / Händler",
    "AUFB" => "Aufbau- Hersteller / Händler",
    "GER" => "Geräte- Hersteller / Händler"];

$NeuRec = "<a href='FirmenEdit.php?ID=0' >Neue Daten eingeben</a>";

require $path2ROOT . 'src/Core/Services/ListFuncsLib.php';

HTML_trailer();

?>
