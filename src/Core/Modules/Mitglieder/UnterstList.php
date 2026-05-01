<!DOCTYPE html>
<?php
# session_start();
/**
 * Unterstützer Verwaltung Liste
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
        file_put_contents(__DIR__ . '/Unterst_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'ADM-Unterst';
$sub_mod = "LIST";

$Zugr = "ADM-MI" ;

$tabelle = 'fv_unterst';// <?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/UnterstList_php-error.log.txt');
# var_dump($_SERVER);

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../../";

// AUTOLOADER für Composer-Klassen laden
require $path2ROOT . 'vendor/autoload.php';

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

require $path2ROOT . 'vendor/autoload.php';

$ber = userBerechtigtOK($Zugr);
if (!$ber) {
    header("Location $path2ROOT/public/");
}

use Fharch\Core\Database\DB_GenericLog;

$header =   ""; 
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$ListHead = "Unterstützer- Verwaltung - Administrator ";
$title = "Unterstützer Daten";
# $TABU = true;
$TABUcss = true;
HTML_header('Unterstützer- Verwaltung', $header, 'Admin', '200em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

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
    header("Location: /src/Core/Controllers/MainMenu.php");
}

$NeuRec = " <a href='UnterstEdit.php?ID=0' >Neuen Unterstützer eingeben</a>";
# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";
$list_ID = 'UN';
$lTitel = ["Alle" => "Alle jemals eingtragenen Unterstützer",
    "Aktive" => "Aktive Unterstützer   ",
    "InAktive" => "In- Aktive Unterstützer   ",
    "WeihnP" => "Für den Versand vorgesehene Einträge    ",
    "AdrListE" => "Adress-Liste für die Aussendung, Änderungen  ",
    "AdrListV" => "Adress-Liste für die Aussendung, Versand    "];

require $path2ROOT . 'src/Core/Services/ListFuncsLib.php';

HTML_trailer();

?>