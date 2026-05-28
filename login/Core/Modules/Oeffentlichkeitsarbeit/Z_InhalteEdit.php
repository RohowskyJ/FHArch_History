<?php

/**
 * Zeitungs- InhalteTermine-, Wartung
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
        file_put_contents(__DIR__ . '/ZI_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'OEF';
$sub_mod = 'ZE';

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
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $ih_id = intval($_GET['ID']);
} else {
    $ih_id = 0;
}
if (isset($_GET['ztNr'])) {
    $zt_id = intval($_GET['ztNr']);
} else {
    $zt_id = 0;;
}
if (isset($_POST['ih_id'])) {
    $ih_id = intval($_POST['ih_id']);
}
if (isset($_POST['zt_id'])) {
    $zt_id = intval($_POST['zt_id']);
}

$DBD = new DB_GenericLog();
#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
#var_dump($meta);

$columnsByTables = $meta->getColumnsForTables(['oe_zeitung_'.$zt_id ]);
#var_dump($columnsByTables);
#var_dump($meta);
$links = new DB_Oeffentlich($DBD);
#var_dump($links);
#var_dump($_SERVER);

$Err_Msg = "";
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($ih_id == 0) {
        $neu = array(
        'ih_id' => 0,
        'ih_zt_id' => $zt_id,
        'ih_jahrgang' => "",
        "ih_jahr" => "",
        'ih_nr' => "",
        'ih_kateg' => "",
        'ih_sg' => "",
        "ih_ssg" => "",
        'ih_gruppe' => "",
        'ih_titel' => "",
        'ih_titelerw' => "",
        'ih_autor'   => "",  
        'ih_email'  => "",
        'ih_tel' => "",
        'ih_fax' => "",
        'ih_seite' => "",
        'ih_spalte' => "",
        'ih_fwehr' => "",
        'ih_changed_id'  => "",
        'ih_changed_at' => ""
        );
    } else {

        $neu_0 = $links->getZInhalteById($ih_id, $zt_id );
        
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

    $neu['zt_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    
    #$neu['zt_erstausgdat'] = convertInternationalDateToSql($neu['zt_erstausgdat']);
    #$neu['zt_letztausgabe'] = convertInternationalDateToSql($neu['zt_letztausgabe']);
    
    foreach ($neu as $key => $val) {
        if (substr($key,0,3) != "ih_") {
            unset($neu[$key]);
        }
    }
    
    date_default_timezone_set('Europe/Berlin');
    
    $neu['ih_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    $neu['ih_changed_at'] = date("Y-m-d h:i");
    
    if ($neu['ih_zt_id'] == 0) { # neuengabe
        $ret = $links->createZInhalte($neu, $neu['ih_id']);
    } else { # Update
        $ret = $links->updateZInhalte($neu['ih_id'], $neu, $neu['ih_zt_id'] );
    }

    header("Location: Z_InhalteList.php?ID=".$neu['ih_zt_id']);
}

switch ($phase) {
    case 0:
        require ('Z_InhalteEdit_ph0_inc.php');
        break;
}
HTML_trailer();
?>