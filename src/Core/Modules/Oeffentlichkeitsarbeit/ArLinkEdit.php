<?php

/**
 * Mitgliederverwaltung, Wartung
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
        file_put_contents(__DIR__ . '/fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'OEF';
$sub_mod = 'AR';

$Zugr = "ADM-OEF" ;

# $tabelle = 'fv_mitglieder';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/ArLink_Edit_php-error.log.txt');


/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../../";

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

require $path2ROOT . 'vendor/autoload.php';

$ber = userBerechtigtOK($Zugr);
if (!$ber) {
    header("Location $path2ROOT/public/");
}

$debug = False; // Debug output Ein/Aus Schalter

use Fharch\Core\Database\DB_GenericLog;;
use Fharch\Core\Services\TableColumnMetadata;
use Fharch\Core\Services\FormRendererFlex;
use Fharch\Core\Modules\Oeffentlichkeitsarbeit\API\AR_LinkModule;

$TABUcss = true;
$header = "";
HTML_header('Links zu öffentl. Archiven und Bibliotheken', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

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

$columnsByTables = $meta->getColumnsForTables(['fv_falinks' ]);
#var_dump($columnsByTables);
#var_dump($meta);
$links = new AR_LinkModule($DBD);
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
    $fa_id = $_GET['ID'];
} else {
    $fa_id = "";
}
if (isset($_POST['fa_id'])) {
    $fa_id = $_POST['fa_id'];
}

if ($phase == 99) {
    header('Location: ArLinkList.php');
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($fa_id == 0) {
        $neu['fa_id'] = $fa_id;
        $neu['fa_link'] = $neu['fa_url_chkd'] = $neu['fa_url_obsolete'] = $neu['fa_text'] = $neu['fa_changed_id'] = $neu['fa_changed_at'] = "";
    } else {

        $neu_0 = $links->getLinksById($fa_id);
        
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
    
    $neu['fa_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    unset($neu['phase']);
   
    if ($neu['fa_id'] == 0) { # neuengabe
        $ret = $this->createLinks($neu);
    } else { # Update
        $ret = $links->updateLinks($neu['fa_id'] , $neu);
    }
    
    header("Location:  ArLinkList.php");
}

switch ($phase) {
    case 0:
        require ('ArLinkEdit_ph0_inc.php');
        break;
}
HTML_trailer();
?>