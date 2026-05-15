<?php

/**
 * Marktplatz-, Wartung
 * 
 * @author Josef Rohowsky - neu 2020
 */
session_start();
echo "<!DOCTYPE html>";
// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MP_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'OEF';
$sub_mod = 'PR';

$Zugr = "ADM-OEF" ;
if ($_SESSION['BS_Prim']['Mod']['smod'] == 'index') {
    $Zugr = "Alle";
}

# $tabelle = 'fv_mitglieder';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/PR_Edit_php-error.log.txt');


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


$debug = False; // Debug output Ein/Aus Schalter

use Fharch\Core\Database\DB_GenericLog;;
use Fharch\Core\Services\TableColumnMetadata;
use Fharch\Core\Services\FormRendererFlex;
use Fharch\Core\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;

$TABUcss = true;
$header = "";
HTML_header('Presse- Spiegel', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

if (isset($_SESSION['BS_Prim']['BE'])) {
    $ber = userBerechtigtOK($Zugr);
    if (!$ber) {
        # header("Location $path2ROOT/VFH");
        return;
    }
}

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

$columnsByTables = $meta->getColumnsForTables(['oe_presse' ]);
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
    $pr_id = $_GET['ID'];
} else {
    $pr_id = "";
}
if (isset($_POST['bd_id'])) {
    $pr_id = $_POST['pr_id'];
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($pr_id == 0) {
        $neu['pr_id'] = $pr_id;
        $neu['pr_datum'] = $neu['pr_name'] = $neu['pr_ausg'] = $neu['pr_medium'] = "";
        $neu['pr_seite'] = $neu['pr_teaser'] = $neu['pr_text'] = $neu['pr_web_seite'] = $neu['pr_url_chkd'] = $neu['pr_url_obsolete'] = $neu['pr_web_text'] = "";
        $neu['bs_bild_1'] = $neu['bs_bild_2'] = $neu['bs_bild_3'] = $neu['bs_bild_4'] = $neu['bs_bild_5'] = $neu['bs_bild_6'] =  $neu['pr_inet'] = "";
        $neu['pr_changed_id'] = $neu['pr_changed_at'] ="";
    } else {

        $neu_0 = $links->getPresseById($pr_id);
        
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
    $neu['pr_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    $neu['pr_changed_at'] = date('Y-m-d H:i',time());

    if ( isset($neu['bild_datei_1']) && $neu['bild_datei_1'] != '') {  // 
        $neu['pr_bild_1'] =  $neu['bild_datei_1'];
    }
    if ( isset($neu['bild_datei_2']) && $neu['bild_datei_2']  != '') {
        $neu['pr_bild_2'] =  $neu['bild_datei_2'];
    }
    if ( isset($neu['bild_datei_3']) && $neu['bild_datei_3']!= '') {
        $neu['pr_bild_3'] =  $neu['bild_datei_3'];
    }
    if ( isset($neu['bild_datei_4']) && $neu['bild_datei_4']!= '') {
        $neu['pr_bild_4'] =  $neu['bild_datei_4'];
    }
    if ( isset($neu['bild_datei_3']) && $neu['bild_datei_5']!= '') {
        $neu['pr_bild_5'] =  $neu['bild_datei_5'];
    }
    if ( isset($neu['bild_datei_6']) && $neu['bild_datei_6']!= '') {
        $neu['pr_bild_6'] =  $neu['bild_datei_6'];
    }
    
    foreach ( $neu as $key => $value) {
        if (substr($key,0,3) != "pr_") {
            unset($neu[$key]);
        }
    }

    if ($neu['pr_id'] == 0) { # neuengabe
        $ret = $this->createPresse($neu);
    } else { # Update
        $ret = $links->updatePresse($neu['pr_id'] , $neu);
    }
    
    header("Location:  PresseList.php");
}

switch ($phase) {
    case 0:
        require ('PresseEdit_ph0_inc.php');
        break;
}
HTML_trailer();
?>