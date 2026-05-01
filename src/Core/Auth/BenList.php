<!DOCTYPE html>
<?php
# session_start();
/**
 * Benutzer Verwaltung Liste
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
        file_put_contents(__DIR__ . '/BEN_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'ADM-Benu';
$sub_mod = "LIST";

$Zugr = "ADM-ALL";

$tabelle = 'fv_benutzer';// <?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/Ben_List_php-error.log.txt');
# var_dump($_SERVER);

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../";

$debug = False; // Debug output Ein/Aus Schalter


require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

require $path2ROOT . 'vendor/autoload.php';

$ber = userBerechtigtOK($Zugr);

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

use Fharch\Core\Database\DB_GenericLog;

$header =   ""; 
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$ListHead = "Benutzer- Verwaltung - Administrator ";
$title = "Benutzer- Daten";

$TABUcss = true;
HTML_header('Benutzer- Verwaltung', $header, 'Admin', '200em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

# $logger = new BS_Logger(__DIR__ . '/BenList_debug.log.txt');

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
    header("Location: /src/Core/Controllers/MainMenu.php");
}

$NeuRec = "<a href='BenEdit.php?ID=0'>Neuen Benutzer anlegen</a>";
# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";
$list_ID = 'BE';
$lTitel = ["Alle" => "Alle Benutzer", "Aktiv" => "Aktive Benutzer",
    "InAktiv" => "Nicht- Aktive Benutzer"];

require $path2ROOT . 'src/Core/Services/ListFuncsLib.php';

HTML_trailer();

?>
