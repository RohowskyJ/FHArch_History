<?php

/**
 * Liste der vom Verein verliehenen Ehrungen, Wartung
 *
 * @author Josef Rohowsky - neu 2023
 *
 *
 */
session_start(); # die SESSION am leben halten

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
    
$module = 'MVW';
$sub_mod = 'Ehrg';

$Zugr = 'ADM-MI';

$tabelle = 'fv_mi_ehrung';

const Prefix = '';
/*
// <?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/bootstrap_php-error.log.txt');
*/

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../../";

$debug = False; // Debug output Ein/Aus Schalter


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


HTML_header('Auszeichnungs - Verwaltung', '', 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

initial_debug('POST','GET');
# var_dump($_POST);
// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();
#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
#var_dump($meta);

$columnsByTables = $meta->getColumnsForTables(['fv_mi_ehrung']);
# var_dump($columnsByTables);

$mitgl = new MI_MitgliederModule($DBD);
# var_dump($mitgl);
#var_dump($_SERVER);
#var_dump($_POST);
#var_dump($_GET);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $me_id = $_GET['ID'];
} else {    
    $me_id = 0;
}

if ($phase == 99) {
    header('Location:  MitglEhrgEdit.php?mi_id=' . $_SESSION[$module]['mi_id']);
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($me_id == 0) {

        $neu['me_id'] = $me_id;
        $neu['mi_id'] = $_SESSION[$module]['mi_id'];

        $neu['me_ehrung'] = $neu['me_eh_datum'] = $neu['me_begruendg'] = $neu['me_changed_at'] = "";
        $neu['me_bild1'] = $neu['me_bild2'] = $neu['me_bild3'] = $neu['me_bild4'] = "";
        $neu['me_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    } else {
        try {
            $neu_0 = $mitgl->getMiEhrungById($me_id);
            # var_dump($neu_0);
            $neu = $neu_0[0];
            unset($neu_0);
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }

    }
}

if ($phase == 1) {

    foreach ($_POST as $name => $value) {
        $neu[$name] = $value;
    }
    
    $uploaddir = $path2ROOT . "data/AOrd_Verz/1/MITGL/";
   
    if (! file_exists($uploaddir)) {
        mkdir($uploaddir);
    }
    
    $target1 = "";
    if (! empty($_FILES['uploaddatei_01'])) {
        $pict1 = basename($_FILES['uploaddatei_01']['name']);
        if (! empty($pict1)) {
            $target1 = $uploaddir . basename($_FILES['uploaddatei_01']['name']);
            if (move_uploaded_file($_FILES['uploaddatei_01']['tmp_name'], $target1)) {
                echo "Datei/Bild 1 geladen!<br><br><br>";
                $neu['me_bild1'] = $pict1;
            }
        }
    }

    $target2 = "";
    if (! empty($_FILES['uploaddatei_02'])) {
        $pict2 = basename($_FILES['uploaddatei_02']['name']);
        if (! empty($pict2)) {
            $target2 = $uploaddir . basename($_FILES['uploaddatei_02']['name']);
            if (move_uploaded_file($_FILES['uploaddatei_02']['tmp_name'], $target2)) {
                echo "Datei/Bild 2 geladen!<br><br><br>";
                $neu['me_bild2'] = $pict2;
            }
        }
    }

    $target3 = "";
    if (! empty($_FILES['uploaddatei_03'])) {
        $pict3 = basename($_FILES['uploaddatei_03']['name']);
        if (! empty($pict3)) {
            $target3 = $uploaddir . basename($_FILES['uploaddatei_03']['name']);
            if (move_uploaded_file($_FILES['uploaddatei_03']['tmp_name'], $target3)) {
                echo "Datei/Bild 3 geladen!<br><br><br>";
                $neu['me_bild3'] = $pict3;
            }
        }
    }

    $target4 = "";
    if (! empty($_FILES['uploaddatei_04'])) {
        $pict4 = basename($_FILES['uploaddatei_04']['name']);
        if (! empty($pict4)) {
            $target4 = $uploaddir . basename($_FILES['uploaddatei_04']['name']);
            if (move_uploaded_file($_FILES['uploaddatei_04']['tmp_name'], $target4)) {
                echo "Datei/Bild 4 geladen!<br><br><br>";
                $neu['me_bild4'] = $pict4;
            }
        }
    }

    unset($neu['MAX_FILE_SIZE']);
    unset($neu['phase']);
    
    $mi_id = intval($neu['mi_id']);
    $me_id = intval($neu['me_id']);
    
    if ($neu['me_id'] == 0) { # neueingabe
        
        try {
            $neu['mi_insert_id'] = $insert_id = $mitgl->createMiEhrung($neu);
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }

    } else { # update
       
        try {
            $neu['me_insert_id'] = $insert_id = $mitgl->updateMiEhrung($me_id, $neu);
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }

    }
    if ($sub_mod = 'ehrg') {
        header("Location: MitglEdit.php?ID=$mi_id");
    } else {
        header("Location: MitglEhrgList.php");
    }
}

switch ($phase) {
    case 0:
        require 'MitglEhrgEdit_ph0.inc.php';
        break;
}
HTML_trailer();
?>