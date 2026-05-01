<?php

/**
 * Anmeldung eines neuen Mitgliedes
 *
 * @author Josef Rohowsky - neu 2018
 *
 *
 */
session_start();

$module = 'MVW';
$sub_mod = 'ExtStart';

$Zugr = "Alle";

$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_mod, 'caller' => $module];
/*
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/VS_Anmeld_php-error.log.txt');
*/

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../";

$debug = false; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

require $path2ROOT . 'login/Basis/common/PHP_MailFuncsLib.php';

# require $path2ROOT . 'login/common/API/FS_StaatenAutoComp_API.php';
/*
require $path2ROOT . 'login/common/BA_Funcs.lib.php';
*/
require $path2ROOT . 'vendor/autoload.php';

$ber = userBerechtigtOK($Zugr);
if (!$ber) {
    header("Location $path2ROOT/public/");
}

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Services\TableColumnMetadata;
use Fharch\Core\Services\FormRendererFlex;
use Fharch\Core\Modules\Mitglieder\API\MI_MitgliederModule;

$miBeitr = ''; // Mitgliedsbeitrag, wird aus config_s_?.ini gesetzt

$header = "";
HTML_header('Mitglieder- Verwaltung', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width


if (! isset($_SESSION[$module])) {
    $_SESSION[$module][$sub_mod] = array();
}
// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================
$DBD = new DB_GenericLog();
#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
#var_dump($meta);

$columnsByTables = $meta->getColumnsForTables(['fv_mi_anmeld']);
#var_dump($columnsByTables);
# var_dump($meta);
$mitgl = new MI_MitgliederModule($DBD);
# var_dump($mitgl);
# var_dump($_SERVER);

// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}

if ($phase == 99) {
    header('Location: ../');
}
console_log(__LINE__ .  'läuft noch');
$err = $mail_err = "";
if (! isset($Err_msg)) {
    $Err_msg = array();
}

# --------------------------------------------------------
# Lesen der Daten aus der sql Tabelle
# ------------------------------------------------------------------------------------------------------------

# Tabellen_Spalten_parms($db, 'fh_mitglieder');
#error_log('vor phase = 0, Zeile 0115');
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    $neu = array(
        "mi_neu_id" => 0,
        'mi_neu_chkd' => 'N',
        'mi_id' => 0,
        'mi_mtyp' => 'UM',
        'mi_org_typ' => '',
        'mi_org_name' => '',
        'mi_name' => '',
        'mi_vname' => '',
        'mi_titel' => '',
        'mi_dgr' => '',
        'mi_n_titel' => '',
        'mi_anrede' => 'Hr.',
        'mi_gebtag' => '',
        'mi_staat' => '',
        'mi_plz' => '',
        'mi_ort' => '',
        'mi_anschr' => '',
        'mi_tel_handy' => '',
        'mi_fax' => '',
        'mi_email' => '',
        'mi_ref_int_2' => '',
        'mi_ref_int_3' => '',
        'mi_ref_int_4' => ''
    );

}

if ($phase == 1) {
    $mail_err = $err = "";
    $err_anz = 0;

    $Err_msg = array();

    foreach ($_POST as $name => $value) {
        $neu[$name] = trim($value);
    }
    $neu['mi_eintrdat'] = date('Y-m-d');

    if ($neu['mi_mtyp'] == "UM" && $neu['mi_org_name'] != "") {
        $neu['mi_mtyp'] = "FG";
    }

    if ($neu['mi_org_typ'] !=  "Privat") {
        if ($neu['mi_org_name'] =="") {
            $Err_msg['mi_org_typ'] = 'Oranisationsamen eingeben';
        }
    }

    if (! isset($neu['mi_sterbdat'])) {
        $refl = $sterb = $austr = "";

        $neu['mi_sterbdat'] = NULL;
        $neu['mi_austrdat'] = NULL;
        $neu['mi_ref_leit'] = NULL;
        $now_mi_id = "";
        $neu['mi_email_status'] = NULL;
        $neu['mi_m_beitr_bez'] = NULL;
        $neu['mi_m_abo_bez'] = NULL;
        $neu['mi_m_beitr_bez_bis'] = NULL;
        $neu['mi_m_abo_bez_bis'] = NULL;
        $neu['mi_abo_ausg'] = NULL;
    }

    if ($neu['mi_gebtag'] == "")  {
        $Err_msg = "Bitte den Geburtstag eingeben";
    } else {
        $neu['mi_gebtag'] = convertInternationalDateToSql($neu['mi_gebtag'], $assumeUSFormat = false);
    }

    if ($neu['mi_email'] != "") {
        if (! filter_var($neu['mi_email'], FILTER_VALIDATE_EMAIL)) {
            $mail_err = "Invalid email format<br>";
            $Err_msg['mi_mail'] = "Invalid email format<br>";
            $err_anz ++;
        }
        
        if ($mitgl->emailExists($neu['mi_email'])) {
            $mail_err = "E-Mail Adresse bereits vorhanden<br>";
            $Err_msg['mi_email'] = "E-Mail Adresse bereits vorhanden<br>";
            $err_anz++;
        }
    }
    
    if (isset($neu['einverkl']) && $neu['einverkl'] == "Y") {
        $neu['mi_einversterkl'] = $neu['einverkl'];
        $neu['mi_einv_art'] = "ONL";
        $neu['mi_einv_dat'] = date('Y-m-d');
    } else { 
        $err .= "Einverständniserklärung wird laut DSGVO zwingend benötigt <br>";
        $Err_msg['mi_einversterkl'] = "Einverständniserklärung wird laut DSGVO zwingend benötigt";
        $err_anz ++;
    }
    

    if ($err_anz != "0") {
        $phase = 0;
    }
    if (count($Err_msg) >= 1) {
        $phase = 0;
    }
}

switch ($phase) {
    case 0:
        require 'MitglAnmeld_ph0.inc.php';
        break;
    case 1:
        require "MitglAnmeld_ph1.inc.php";
        break;
}
HTML_trailer();
?>
