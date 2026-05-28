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
 
$Zugr = "ADM-PSA" ;
if (!isset($_SESSION['BS_Prim']['BE'])) {
    $Zugr = "Alle";
}
$message =  __FILE__.  " " . __LINE__ . " Zugr $Zugr";
file_put_contents('/userber_error.log', $message, FILE_APPEND);
$tabelle = 'pso_ort-ref';// <?php

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../../";

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/OR_OrtList_php-error.log.txt');
# var_dump($_SERVER);

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/Core/Services/FuncsLib.php';
require $path2ROOT . 'login/Core/Services/CommFuncsLib.php';
require $path2ROOT . 'login/Core/Services/ConstLib.php';

require $path2ROOT . 'vendor/autoload.php';

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Auth\Auth;

$proj = $_GET['proj'] ?? null;  // proj = EHRZ | AERM

if (is_null($proj)) { echo "Fehler: falsche Parameter- Übergabe Orts- Auswahl für PSA. Abbruch. "; exit; }

$message = __LINE__ . " vor Abfrage ";
if ($Zugr == 'Alle') {
    $message =  __LINE__."/n";
    file_put_contents('ortlist_error.log', $message, FILE_APPEND);
} else {
    if (isset($_SESSION['BS_Prim']['BE'])) {
        $ber = userBerechtigtOK($Zugr);
        echo __LINE__ . " zugr $Zugr /n";
        var_dump($ber);
        file_put_contents('ortlist_error.log', $message, FILE_APPEND);
#exit;
        if (!$ber) {
            $bPath = $_SESSION['BS_Prim']['Env']['basePath'];
            echo "Zugriff nicht erlaubt. Abbruch. ";
            $bPath = "";
            if ($_SESSION['BS_Prim']['Env']['basePath'] != "") {
                $bPath = "/" . $_SESSION['BS_Prim']['Env']['basePath'];
            }

            echo '<script type="text/javascript">
                   setTimeout(function() {
                   window.location.href = "<?php echo $bPath ?>/VFH";
                   }, 2000);
                 </script>';
            return;
            
        }
        
    }
}

$header =   ""; 
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$ListHead = "Ortsauswahl";
$title = "Ortsauswahl";

HTML_header('Ortsauswahl', $header, 'Admin', '80em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

$moduleId = $module."-".$sub_mod;
// Eigene Meldung mit Modulkennung loggen
# $logger->log('Starte Verarbeitung des Moduls', $moduleId, basename(__FILE__));

// XR_Database mit bestehender PDO-Instanz initialisieren
$DBD = new DB_GenericLog();
# var_dump($DBD);
$pdo = $DBD->getPDO();
# var_dump($pdo);

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";

$list_ID = 'OR';

if ($proj == 'AERM') {  // Anzeige der Liste für Ehrungen
    $lTitel = ["Alle" => "Alle Orte "];
    if ($Zugr == 'Alle') {
        $lTitel = ["Alle." => "Alle Orte "];
    }
} else { // Anzeige der Liste für Ärmelabzeichen
    /*
    if (!isset($_SESSION['BS_Prim']['BE'])) {
        echo "Zugriff nicht erlaubt. Abbruch. ";
        $bPath = $_SESSION['BS_Prim']['Env']['basePath'];
        echo '<script type="text/javascript">
        setTimeout(function() {
          window.location.href = "/fharch-oop/VFH";
        }, 400);
      </script>';
            return;
    }
    */
    $lTitel = ["Alle" => "Alle Ehrungen ",
               "Bund" => "vom Bund (Staat) ausgegebenen Auszeichnungen ",
               "Bdld" => "vom Bundesland ausgegebenen Auszeichnungen ",
               "Gde"  => "von einer Gemeinde ausgegebenen Auszeichnungen ",
               "Vereine" => "von einem Verein ausgegebenen Auszeichnungen ",
              ];
}

$NeuRec = "";
if (isset($_SESSION['BS_Prim']['BE'])) {
    $NeuRec = " &nbsp; &nbsp; &nbsp; <a href='OrtsEdit.php?ID=0' > Neuen Datensatz anlegen </a>";
}

require $path2ROOT . 'login/Core/Services/ListFuncsLib.php';

HTML_trailer();

?>