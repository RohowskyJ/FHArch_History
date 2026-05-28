<?php

/**
 * Buch- Rezensionen, Wartung
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
        file_put_contents(__DIR__ . '/BU_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'OEF-BU';
$sub_mod = 'BU';

$Zugr = "ADM-OEF" ;
if ($_SESSION['BS_Prim']['Mod']['smod'] == 'index') {
    $Zugr = "Alle";
}

# $tabelle = 'fv_mitglieder';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/BU_Edit_php-error.log.txt');


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

$header = "";
HTML_header('Buch- Rezensionen', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

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

$columnsByTables = $meta->getColumnsForTables(['oe_buecher' ]);
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
    $bu_id = $_GET['ID'];
} else {
    $fbu_id = "";
}
if (isset($_POST['bu_id'])) {
    $bu_id = $_POST['bu_id'];
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($bu_id == 0) {
        $neu['bu_id'] = $bu_id;
        $neu['bu_titel'] = $neu['bu_utitel'] = $neu['bu_author'] = $neu['bu_verlag'] = "";
        $neu['bu_isbn'] = $neu['bu_preis'] = $neu['bu_seiten'] = $neu['bu_bilder_anz'] = $neu['bu_bilder_art'] = "";
        $neu['bu_format'] = $neu['bu_teaser'] = $neu['bu_text'] = $neu['bu_bild_1'] = $neu['bu_text_1'] = "";
        $neu['bu_bild_2'] = $neu['bu_text_2'] = $neu['bu_bild_3'] = $neu['bu_text_3'] = $neu['bu_bild_4'] = $neu['bu_text_4'] = "";
        $neu['bu_bild_5'] = $neu['bu_text_5'] = $neu['bu_bild_6'] = $neu['bu_text_6'] =  "";
        $neu['bu_bew_ges'] = $neu['bu_bew_bild'] = $neu['bu_txt'] = $neu['bu_editor'] =  "";
        $neu['bu_edit_dat'] = $neu['bu_frei_dat'] = '0000-00-00';
        $neu['bu_ed_id'] = $neu['bu_frei_id'] = 0;
        $neu['bu_frei_stat'] = "";
        $neu['bu_changed_id'] = $neu['bu_changed_at'] = "";
    } else {

        $neu_0 = $links->getBuecherById($bu_id);
        
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
    
    if ( isset($neu['bild_datei_1']) &&  $neu['bild_datei_1'] != '') {
        $neu['bu_bild_1'] =  $neu['bild_datei_1'];
    }
    if (  isset($neu['bild_datei_2']) && $neu['bild_datei_2'] != '') {
        $neu['bu_bild_2'] =  $neu['bild_datei_2'];
    }
    if ( isset($neu['bild_datei_3']) &&  $neu['bild_datei_3'] != '') {
        $neu['bu_bild_3'] =  $neu['bild_datei_3'];
    }
    if ( isset($neu['bild_datei_4']) &&  $neu['bild_datei_4'] != '') {
        $neu['bu_bild_4'] =  $neu['bild_datei_4'];
    }
    if ( isset($neu['bild_datei_5']) &&  $neu['bild_datei_5'] != '') {
        $neu['bu_bild_5'] =  $neu['bild_datei_5'];
    }
    if ( isset($neu['bild_datei_6']) &&  $neu['bild_datei_6'] != '') {
        $neu['bu_bild_6'] =  $neu['bild_datei_6'];
    }
    
    $neu['bu_ed_id'] = intval($neu['bu_ed_id']);
    $neu['bu_frei_id'] = intval($neu['bu_frei_id']);
    
    if (is_null($neu['bu_frei_dat'])) {
        $neu['bu_frei_dat'] = '0000-00-00';
    }
    
    foreach($neu as $inx => $val) {
        if (substr($inx, 0, 3) == 'bu_') {
            continue;
        }
        unset($neu[$inx]) ;   
    }
    
    $neu['bu_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    unset($neu['phase']);
   
    if ($neu['bu_id'] == 0) { # neuengabe
        $ret = $this->createBuecher($neu);
    } else { # Update
        $ret = $links->updateBuecher($neu['bu_id'] , $neu);
    }
    
    header("Location:  BuchList.php");
}

switch ($phase) {
    case 0:
        require ('BuchEdit_ph0_inc.php');
        break;
}
HTML_trailer();
?>