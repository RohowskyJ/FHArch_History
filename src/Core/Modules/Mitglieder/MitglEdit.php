<?php

/**
 * Mitgliederverwaltung, Wartung
 * 
 * @author Josef Rohowsky - neu 2020
 */
session_start();

$module = 'MVW';
$sub_mod = 'all';

$Zugr = 'ADM-MI';
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

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

require $path2ROOT . 'vendor/autoload.php';

$ber = userBerechtigtOK($Zugr);
if (!$ber) {
    header("Location $path2ROOT/public/");
}

$debug = False; // Debug output Ein/Aus Schalter

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Services\TableColumnMetadata;
use Fharch\Core\Services\FormRendererFlex;
use Fharch\Core\Modules\Mitglieder\API\MI_MitgliederModule;


$TABUcss = true;
$header = "";
HTML_header('Mitglieder- Verwaltung', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

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

$columnsByTables = $meta->getColumnsForTables(['fv_mitglieder', 'fv_mi_ehrung', 'fv_mi_bez' ]);
#var_dump($columnsByTables);
# var_dump($meta);
$mitgl = new MI_MitgliederModule($DBD);
#var_dump($mitgl);
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
    $mi_id = $_GET['ID'];
} else {
    $mi_id = "";
}
if (isset($_POST['mi_id'])) {
    $mi_id = $_POST['mi_id'];
}

if ($phase == 99) {
    header('Location: VS_M_List.php');
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($mi_id == 0) {
        $neu['mi_id'] = $mi_id;
        $neu['mi_anrede'] = "Hr.";
        $neu['mi_mtyp'] = $neu['mi_org_typ'] = $neu['mi_org_name'] = $neu['mi_name'] = $neu['mi_vname'] = $neu['mi_titel'] = "";
        $neu['mi_n_titel'] = $neu['mi_dgr'] = $neu['mi_gebtag'] = $neu['mi_plz'] = $neu['mi_ort'] = $neu['mi_anschr'] = "";
        $neu['mi_staat'] = "AT";
        $neu['staat'] = 'Österreich';
        $neu['mi_tel_handy'] = $neu['mi_handy'] = $neu['mi_fax'] = $neu['mi_email'] = $neu['mi_email_status'] = $neu['mi_vorst_funct'] = $neu['mi_ref_leit'] = "";
        $neu['mi_ref_int_2'] = $neu['mi_ref_int_3'] = $neu['mi_ref_int_4'] = "";
        $neu['mi_sterbdat'] = $neu['mi_beitritt'] = $neu['mi_austrdat'] = $neu['mi_m_beitr'] = $neu['mi_m_abo'] = $neu['mi_m_beitr_bez'] = $neu['mi_m_abo_bez'] = $neu['mi_abo_ausg'] = "";
        $neu['mi_einv_art'] = $neu['mi_einversterkl'] = $neu['mi_einv_dat'] = $neu['mi_changed_id'] = $neu['mi_changed_at'] = "";
    } else {

        $neu_0 = $mitgl->getMitgliedById($mi_id);
        
        $neu = $neu_0[0];
        unset($neu_0);
        
        $neu['staat_id'] = ''; 
        $neu['staat'] = ""; //Auslesen!
        
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

    foreach ($_POST as $name => $value) {
        $neu[$name] = $value;
    }
}

switch ($phase) {
    case 0:
        require ('MitglEdit_ph0.inc.php');
        break;
    case 1:
        require "MitglEdit_ph1.inc.php";
        break;
}
HTML_trailer();
?>