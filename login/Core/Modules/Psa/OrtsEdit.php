<?php
/** Wartung der Orts- Daten 
 *
 * @author Josef Rohowsky - neu 2020
 */
session_start();

$module = 'PSA-ORT';
$sub_mod = 'Edit';

$Zugr = 'ADM-PSA';
/*
 error_reporting(E_ALL);
 ini_set('display_errors', '0');
 ini_set('log_errors', '1');
 ini_set('error_log', __DIR__ . '/bootstrap_php-error.log.txt');
 */

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

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Services\TableColumnMetadata;
use Fharch\Core\Services\FormRendererFlex;
use Fharch\Core\Modules\PSA\API\DB_Psa;


$header = "";
HTML_header('Mitglieder- Verwaltung', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

if (isset($_SESSION['BS_Prim']['BE'])) {
    $ber = userBerechtigtOK($Zugr);
    if (!$ber) {
        # header("Location $path2ROOT/VFH");
        return;
    }
}
var_dump($_POST);
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

$columnsByTables = $meta->getColumnsForTables(['pso_ort_ref', 'psw_aermel_abz', 'psw_ff_wappen', 'psw_ort_wappen' ]);
#var_dump($columnsByTables);
# var_dump($meta);
$psa = new DB_Psa($DBD);
#var_dump($mitgl);
#var_dump($_SERVER);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================

$phase = isset($_POST['phase']) ? $_POST['phase'] : 0;
/*
$fw_id = isset($_GET['ID']) ? $_GET['ID'] : 0;

if (isset($_POST['fw_id'])) {
    $fw_id = $_POST['fw_id'];
}
*/
$proj= isset($_GET['proj']) ? $_GET['proj'] : 0;

if (isset($_POST['proj'])) {
    $proj = $_POST['proj'];
}

$fw_id = isset($_POST['fw_id']) ? intval($_POST['fw_id']) : (isset($_GET['ID']) ? $_GET['ID'] : 0);

echo __LINE__ . " fw_id $fw_id proj $proj<br>";
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($fw_id == 0) {
        $neu = [
        'fw_id' => 0,
        'fw_st_abk' => "AT",
        "fw_bd_abk" => "NOE",
        'fw_bz_abk' => "",
        'fw_bz_name' => "",
        'fw_ab_nr' => "",
        'fw_ab_name' => "",
        'fw_gd_nr' => "",
        "fw_gd_name" => "",
        'fw_gd_art' => "",
        'fw_fw_nr' => "",
        'fw_fw_name' => "",
        'fw_fw_typ' => "",
        'fw_grdg_dat' => "",
        'fw_end_dat' => "",
        'fw_kommentar' => "",
        'fw_auszeich' => '',
        'fw_aermelw' => '',
        'fw_ort_komm' => "",
        "fw_aenddat" => "",
        'fw_changed_id' => '',
        'fw_changed_at' => ""
        ];
    } else {
        
        $neu = $psa->getOrtById($fw_id);
        
        $neu['staat_id'] = '';
        $neu['staat'] = ""; //Auslesen!
        
    }
}

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }
    
    date_default_timezone_set('Europe/Berlin');
    
    $neu['fw_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    
    if ($neu['staat_id'] != '') {
        $neu['st_abkzg'] = $neu['staat_id'];
    }
    if ($neu['bdld_id'] != '') {
        $neu['fbl_blabkz'] = $neu['bdld_id'];
    }
 
    foreach ( $neu as $key => $val) {
        if (substr($key,0,3) != 'fw_') {
            unset($neu[$key]);
        }
    }
    
    if ($neu['fw_id'] == 0) { # neuengabe
        $ret = $psa->createOrt($neu);
    } else { # Update
        $ret = $psa->updateOrt($neu['fw_id'] , $neu);
    }
    
    header("Location:  MuseenList.php");
}

switch ($phase) {
    case 0:
        require ('OrtsEdit_ph0_inc.php');
        break;
}
HTML_trailer();
?>