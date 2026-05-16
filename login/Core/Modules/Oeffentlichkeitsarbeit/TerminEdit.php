<?php

/**
 *Termine-, Wartung
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
$sub_mod = 'TE';

$Zugr = "ADM-OEF" ;
if ($_SESSION['BS_Prim']['Mod']['smod'] == 'index') {
    $Zugr = "Alle";
}

# $tabelle = 'fv_mitglieder';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MP_Edit_php-error.log.txt');


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
HTML_header('Termine - Veranstaltungen', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

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

$columnsByTables = $meta->getColumnsForTables(['oe_va_termine' ]);
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
    $va_id = intval($_GET['ID']);
} else {
    $va_id = 0;
}
if (isset($_POST['va_id'])) {
    $va_id = intval($_POST['va_id']);
}

$Err_Msg = "";
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($va_id == 0) {
        $neu['va_id'] = $va_id;
        $neu['va_datum'] = $neu['va_end_dat'] = $neu['va_anmeld_end'] = "" ; //"00-00-0000";
        $neu['va_begzt'] = $neu['va_endzt'] = "00:00";
        $neu['va_dauer'] = $neu['va_titel'] = $neu['va_beschr'] = "";
        $neu['va_kateg'] = "";
        $neu['va_anm_erf'] = "N";
        $neu['va_umfang']  = "9";
        $neu['va_inst'] = $neu['va_adresse'] = $neu['va_ort'] = $neu['va_staat'] = $neu['va_bdld'] =  "";
        $neu['va_beitrag_m'] = $neu['va_beitrag_g'] = 0;
        $neu['va_admin_email'] = $neu['va_kontakt'] = $neu['va_link_einladung'] = "";
        $neu['va_bild_1'] = $neu['va_bild_2'] = $neu['va_bild_3'] = $neu['va_bild_4'] = "";
        $neu['va_internet'] = $neu['va_url_chkd'] = $neu['va_url_obsolete'] = $neu['va_anm_text'] =  "";
        $neu['va_plaetze'] = $neu['va_warte'] = $neu['va_akt_pl'] = $neu['va_wl_pl'] = $neu['va_anz_anmeld'] =  0;
        $neu['va_freigabe_id'] = $neu['va_freigabe_at'] = $neu['va_abschluss_id'] = $neu['va_abschluss_at'] = $neu['va_storno_id'] = $neu['va_storno_at'] =  "";
        $neu['va_created_id'] = $neu['va_created_at'] = $neu['va_changed_id'] = $neu['va_changed_at'] = "";
    } else {

        $neu_0 = $links->getTermineById($va_id);
        
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
    unset($_SESSION[$module]['Pct_Arr']);
}

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }

    $neu['va_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    unset($neu['phase']);

    if ( isset($neu['bild_datei_1']) && $neu['bild_datei_1'] != '') {  // 
        $neu['va_bild_1'] =  $neu['bild_datei_1'];
    }
    if ( isset($neu['bild_datei_2']) && $neu['bild_datei_2']  != '') {
        $neu['va_bild_2'] =  $neu['bild_datei_2'];
    }
    if ( isset($neu['bild_datei_3']) && $neu['bild_datei_3']!= '') {
        $neu['va_bild_4'] =  $neu['bild_datei_3'];
    }
    if ( isset($neu['bild_datei_1']) && $neu['bild_datei_1']!= '') {
        $neu['va_bild_4'] =  $neu['bild_datei_4'];
    }
    
    $neu['va_datum'] = convertInternationalDateToSql($neu['va_datum']);
    $neu['va_end_dat'] = convertInternationalDateToSql($neu['va_end_dat']);
    $neu['va_anmeld_end'] = convertInternationalDateToSql($neu['va_anmeld_end']);
    
    
    unset($neu['bild_datei_1']);
    unset($neu['urheinfueg_1']);
    unset($neu['upload_method_1']);
    
    unset($neu['bild_datei_2']);
    unset($neu['urheinfueg_2']);
    unset($neu['upload_method_2']);
    
    unset($neu['bild_datei_3']);
    unset($neu['urheinfueg_3']);
    unset($neu['upload_method_3']);
    
    unset($neu['bild_datei_4']);
    unset($neu['urheinfueg_4']);
    unset($neu['upload_method_4']);
    
    unset($neu['MAX_FILE_SIZE']);
    unset($neu['pic_cnt']);
    unset($neu['unload_metnod']);
    
    unset($neu['reSize']);
    unset($neu['phase']);
   
    unset($neu['staat']);
    unset($neu['bdld']);
    unset($neu['staat_id']);
    unset($neu['bdld_id']);
    
    if ($neu['va_id'] == 0) { # neuengabe
        $neu['va_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['va_created_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['va_changed_at'] = date("Y-m-d h:i");
        $neu['va_created_at'] = date("Y-m-d h:i");
        $ret = $links->createTermine($neu);
    } else { # Update
        $neu['va_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['va_changed_at'] = date("Y-m-d h:i");
        $ret = $links->updateTermine($neu['va_id'] , $neu);
    }

    header("Location: TerminList.php");
}

switch ($phase) {
    case 0:
        require ('TerminEdit_ph0_inc.php');
        break;
}
HTML_trailer();
?>