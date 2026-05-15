<?php

/**
 * Dokumentationen-, Wartung
 * 
 * @author Josef Rohowsky - neu 2020
 */
session_start();

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/dk_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'OEF-DO';
$sub_mod = 'DO';

$Zugr = "ADM-OEF" ;
if ($_SESSION['BS_Prim']['Mod']['smod'] == 'index') {
    $Zugr = "Alle";
}

# $tabelle = 'fv_mitglieder';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/dk_Edit_php-error.log.txt');


/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../../";

require $path2ROOT . 'login/Core/Services/FuncsLib.php';
require $path2ROOT . 'login/Core/Services/CommFuncsLib.php';
require $path2ROOT . 'login/Core/Services/ConstLib.php';

require $path2ROOT . 'vendor/autoload.php';

if (isset($_SESSION['BS_Prim']['BE'])) {
    $ber = userBerechtigtOK($Zugr);
    if (!$ber) {
        # header("Location $path2ROOT/VFH");
        return;
    }
}

$debug = False; // Debug output Ein/Aus Schalter

use Fharch\Core\Database\DB_GenericLog;;
use Fharch\Core\Services\TableColumnMetadata;
use Fharch\Core\Services\FormRendererFlex;
use Fharch\Core\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;

$TABUcss = true;
$header = "";
HTML_header('Dokumentationen', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();
#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
#var_dump($meta);

$columnsByTables = $meta->getColumnsForTables(['oe_dokumente' ]);
#var_dump($columnsByTables);
#var_dump($meta);
$links = new DB_Oeffentlich($DBD);
#var_dump($links);
#var_dump($_SERVER);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $dk_id = $_GET['ID'];
} else {
    $dk_id = "";
}
if (isset($_POST['dk_id'])) {
    $dk_id = $_POST['dk_id'];
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($dk_id == 0) {
        $neu['dk_id'] = $dk_id;
        $neu['dk_thema'] = $neu['dk_titel'] = $neu['dk_author'] = $neu['dk_urspr'] = "";
        $neu['dk_dsn'] = $neu['dk_dsn_2'] = $neu['dk_path2dsn'] = $neu['dk_url'] = $neu['dk_sg'] = "";
        $neu['dk_changed_id'] = $neu['dk_changed_at'] = "";
    } else {

        $neu_0 = $links->getDokusById($dk_id);
        
           $neu = $neu_0[0];
           unset($neu_0[0]);
        # var_dump($neu);
        
        if ($debug) {
            echo '<pre class=debug>';
            echo '<hr>$neu: ';
            var_dump($neu);
            echo '</pre>';
        }
    }
}

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }
    
    date_default_timezone_set('Europe/Berlin');
    
    
    foreach($neu as $inx => $val) {
        if (substr($inx, 0, 3) == 'dk_') {
            continue;
        }
        unset($neu[$inx]) ;   
    }
    
    $neu['dk_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    unset($neu['phase']);
   
    if ($neu['dk_id'] == 0) { # neuengabe
        $ret = $this->createDokus($neu);
    } else { # Update
        $ret = $links->updateDokus($neu['dk_id'] , $neu);
    }
    
    header("Location:  DokuList.php");
}

switch ($phase) {
    case 0:
        require ('DokuEdit_ph0_inc.php');
        break;
}
HTML_trailer();
?>