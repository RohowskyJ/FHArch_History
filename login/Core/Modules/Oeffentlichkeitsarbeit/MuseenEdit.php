<?php

/**
 * Museen-, Wartung
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
        file_put_contents(__DIR__ . '/MU_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'OEF-MU';
$sub_mod = 'Edit';

$Zugr = "ADM-OEF" ;
if ($_SESSION['BS_Prim']['Mod']['smod'] == 'index') {
    $Zugr = "Alle";
}

# $tabelle = 'fv_mitglieder';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MU_Edit_php-error.log.txt');


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
HTML_header('Museen', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

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

$columnsByTables = $meta->getColumnsForTables(['oe_museen' ]);
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
    $mu_id = intval($_GET['ID']);
} else {
    $mu_id = 0;
}
if (isset($_POST['mu__id'])) {
    $mu_id = intval($_POST['mu_id']);
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($mu_id == 0) {
        $neu['mu_id'] = $mu_id;
        $neu['mu_staat'] = "AT";
        $neu['mu_bdland'] = "NOE";
        $neu['mu_bez'] = $neu['mu_name'] = $neu['mu_bezeichng'] = $neu['mu_adresse_a'] = $neu['mu_plz_a'] = $neu['mu_ort_a'] = "";
        $neu['mu_adresse_p'] = $neu['mu_plz_p'] = $neu['mu_ort_p'] = $neu['mu_eigner'] = "";
        $neu['mu_kustos_titel'] = $neu['mu_kustos_vname'] = $neu['mu_kustos_name'] = $neu['mu_kustos_dgr'] = $neu['mu_kustos_tel'] = "";
        $neu['mu_kustos_intern'] = $neu['mu_kustos_email'] = $neu['mu_sammelbeg'] = "";
        $neu['mu_bild_1'] = $neu['mu_bild_2'] = $neu['mu_bildbeschr_1']  = $neu['mu_bildbeschr_2']  = $neu['mu_mustyp'] = $neu['mu_museigtyp'] = $neu['mu_sammlgschw'] = "";
        $neu['mu_besobj_1'] = $neu['mu_besobj_2'] = $neu['mu_besobj_3'] = $neu['mu_anz_obj'] = $neu['mu_archiv'] = "";
        $neu['mu_protbuch'] = $neu['mu_abzeich'] = $neu['mu_ausruest'] = $neu['mu_kleinger'] = $neu['mu_grossger'] = "";
        $neu['mu_toilett'] = $neu['mu_garderobe'] = $neu['mu_cafe'] = $neu['mu_sonst_anb'] = $neu['mu_rollst'] = "";
        $neu['mu_beheinr'] = $neu['mu_oeffnung'] = $neu['mu_saison'] = $neu['mu_oez_mo'] = $neu['mu_oez_di'] = "";
        $neu['mu_oez_mi'] = $neu['mu_oez_do'] = $neu['mu_oez_fr'] = $neu['mu_oez_sa'] = $neu['mu_oez_so'] = "";
        $neu['mu_oez_fei'] = $neu['mu_f1_titel'] = $neu['mu_f1_vname'] = $neu['mu_f1_name'] = $neu['mu_f1_tel'] = $neu['mu_f1_dgr'] = "";
        $neu['mu_f1_handy'] = $neu['mu_f1_email'] = $neu['mu_f2_titel'] = $neu['mu_f2_vname'] = $neu['mu_f2_name'] = "";
        $neu['mu_f2_dgr'] = $neu['mu_f2_tel'] = $neu['mu_f2_handy'] = $neu['mu_f2_email'] = $neu['mu_uidaend'] = "";
        $neu['mu_aenddat'] = "";
        $neu['mu_changed_id'] = $neu['mu_changed_at'] = "";
    } else {

        $neu_0 = $links->getMuseenById($mu_id);
        
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
    
    $neu['mu_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];

    if ($neu['staat_id'] != '') {
        $neu['mu_staat'] = $neu['staat_id'];
    }
    if ($neu['bdld_id'] != '') {
        $neu['mu_bdland'] = $neu['bdld_id'];
    }
    if ($neu['mandant_id'] != '') {
        $neu['mu_eigner'] = $neu['mandant_id'];
    }
    
    foreach ( $neu as $key => $val) {
        if (substr($key,0,3) != 'mu_') {
            unset($neu[$key]);
        }
    }
    
    if ($neu['mu_id'] == 0) { # neuengabe
        $ret = $this->createMuseen($neu);
    } else { # Update
        $ret = $links->updateMuseen($neu['mu_id'] , $neu);
    }
    
    header("Location:  MuseenList.php");
}

switch ($phase) {
    case 0:
        require ('MuseenEdit_ph0_inc.php');
        break;
}
HTML_trailer();
?>