<?php

/**
 * Formular für Wappen und Ärmelabzeichen
 * 
 * @author Josef Rohowsky - neu 2019
 */

use Fharch\Core\Services\FormRendererFlex;
use Fharch\Core\Services\AutoCompleteAPI;


if ($debug) {
    echo "<pre class=debug>OrtsEdit_ph0_inc ist gestarted</pre>";
}

$editProtect = true;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

/** Vars für accordion */
$accUpd = 1;

/**Zugriffsteuerung */
if ($Zugr == "Alle") { //  || !userBerechtigtOK($Zugr)
    $editProtect = true;
    # $readonly = true;
    $accUpd = 0; // für accordion
}

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );


/** input für Accordion */
echo "<input type='hidden' id='recId' value='" . $neu['fw_id'] . " >";
echo "<input type='hidden' id='accUpd' value='$accUpd' >";
echo "<input type='hidden' id='proj' name='proj' value='$proj' >";

echo "<div class='white'>";

echo "<input type='hidden' name='fw_id' value='$fw_id'/>";
# =========================================================================================================
echo $forms->renderHeader('Ärmel- Abzeichen von: ');
# =========================================================================================================
/*
echo $forms->renderTextLikeFieldFlex('fw_id');

# =========================================================================================================
echo $forms->renderTrenner('Orts- Daten');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('st_abkzg', 10);
if (!$editProtect) {
    AutoCompForm_Staat();
}

echo $forms->renderTextLikeFieldFlex('bl_blabkz', 2);
if (!$editProtect) {
    AutoCompForm_Bdld('$st');
}

echo $forms->renderTextLikeFieldFlex('fw_bz_abk', 4);
*/
echo $forms->renderTextLikeFieldFlex('fw_bz_name', 50);

echo $forms->renderTextLikeFieldFlex('fw_ab_nr', 4);
echo $forms->renderTextLikeFieldFlex('fw_ab_name', 50);

// echo $forms->renderTextLikeFieldFlex('fw_gd_nr', 4);
echo $forms->renderTextLikeFieldFlex('fw_gd_name', 50);

$gd_art = array(
    '  ' => 'keine Definition',
    'Ss' => 'Statutarstadt',
    'St' => 'Stadtgemeinde',
    'Ma' => 'Marktgemeinde',
    'Ge' => 'Gemeinde',
    'Zt' => 'Gemeinde- Teil',
    'Or' => 'Organisation'
);

echo $forms->renderSelectFieldFlex('fw_gd_art', $gd_art, '');

echo $forms->renderTextLikeFieldFlex('fw_ort_komm');

if ($neu['fw_fw_name'] != "") {
    # =========================================================================================================
    echo $forms->renderTrenner('Feuerwehr ( - Ortsteil)');
    # =========================================================================================================
    
    // echo $forms->renderTextLikeFieldFlex('fw_fw_nr', 4);
    echo $forms->renderTextLikeFieldFlex('fw_fw_name', 50);
    
    echo $forms->renderTextLikeFieldFlex('fw_fw_typ', 5);
    
    echo $forms->renderTextLikeFieldFlex('fw_grdg_dat', 15);
    
    echo $forms->renderTextLikeFieldFlex('fw_end_dat', 15);
    
    echo $forms->renderTextLikeFieldFlex('fw_kommentar');
}

/*
# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fw_changed_id');
echo $forms->renderTextLikeFieldFlex('fw_changed_at');
*/
# =========================================================================================================
/*
if ($Zugr != "Alle" && userBerechtigtOK($Zugr)) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}
*/
echo "<p><a href='OrtsList.php?fw_id=".$neu['fw_id']."&proj=AERM'>Zurück zur Liste</a></p>";

require "AermAbzList_inc.php";
/*
    if ($_SESSION[$module]['proj'] == 'AERM') {
        $g = $f = $a = 0;

        echo "<div class='w3-container'><fieldset> <label> Gemeinde-Wappen: </label><br/>";
        require 'VF_PS_OV_OW_List.inc.php';
        echo "</fieldset></div>";

        echo "<div class='w3-container'><fieldset> <label> Wappen bei der Feuerwehr: </label><br/>";
        require 'VF_PS_OV_FW_List.inc.php';

        echo "</fieldset></div>";

        echo "<div class='w3-container'><fieldset> <label> Ärmel- und T-Shirt- Abzeichen:</label><br/>";
        require ('VF_PS_OV_AB_List.inc.php');
        echo "</fieldset></div>";
    }
+/

/** notwendige css und js einfügen */
echo "<script src='" . $path2ROOT . "login/Core/AllgVerw/js/AutoComp_Staat.js' ></script>";
echo "<script src='" . $path2ROOT . "login/Core/AllgVerw/js/AutoComp_Bdld.js' ></script>";
echo "<script src='" . $path2ROOT . "VFH/js/accordion.min.js' async></script>";
echo "<script src='" . $path2ROOT . "VFH/js/add_accordion.js' async></script>";
echo "<script src='" . $path2ROOT . "VFH/js/VF_toggle.js' async></script>";
echo "<script src='" . $path2ROOT . "VFH/js/VF_Foto_Upl.js' async></script>";
# echo "<script src='" . $path2ROOT . "VFH/js/VF_selFotoLibs.js' async></script>";

/**
 * Diese Funktion verändert die Zellen- Inhalte für die Anzeige in der Liste
 *
 * Funktion wird vom List_Funcs einmal pro Datensatz aufgerufen.
 * Die Felder die Funktioen auslösen sollen oder anders angezeigt werden sollen, werden hier entsprechend geändert
 *
 *
 * @param array    $row
 * @param string   $tabelle
 * @return boolean immer true
 *
 * @global string $path2VF   String zur root-Angleichung für relative Adressierung
 * @global string $T_List    Auswahl der Listen- Art
 * @global string $module    Modul-Name für $_SESSION[$module] - Parameter
 */
function modifyRow(array &$row,$tabelle)
{
    global $path2VF, $T_List, $module;
    $debug = True;
    
    if ($_SESSION[$module]['proj'] == "AUSZ") {
        if ($tabelle == "aw_ort_wappen") {
            $fo_id = $row['fo_id'];
            $row['fo_id'] = "<a href='VF_PS_OV_M_Edit.php?ID=$fo_id' >" . $fo_id . "</a>";
        } elseif ($tabelle == "az_beschreibg") {
            $proj = $_SESSION[$module]['proj'];

            $pict_path = "AOrd_Verz/PSA/AUSZ/" . $_SESSION[$proj]['fw_bd_abk'] . "/Stat/";
            
            $ab_id = $row['ab_id'];
            $row['ab_id'] = "<a href='VF_PS_OV_AD_Edit.php?ID=$ab_id' >" . $ab_id . "</a>";
            $ab_statut = $row['ab_statut'];
            $row['ab_statut'] = "<a href='$pict_path$ab_statut' target='Statut' >" . $ab_statut . "</a>";
            $ab_erklaerung = $row['ab_erklaerung'];
            $row['ab_erklaerung'] = "<a href='$pict_path$ab_erklaerung' target='Erklaerung' >" . $ab_erklaerung . "</a>";
        }
    } elseif ($_SESSION[$module]['proj'] == "AERM") {
        if ($tabelle == "aw_ort_wappen") {
            # echo "L 92: fh_ort_wappen \$tabelle $tabelle <br/>";
            $fo_id = $row['fo_id'];
            $row['fo_id'] = "<a href='VF_PS_OV_OW_Edit.php?ID=$fo_id' >" . $fo_id . "</a>";

            $pict_path = "AOrd_Verz/PSA/AERM/Wappen_Ort/";

            if ($row['fo_gde_wappen'] != "") {
                $fo_gde_wappen = $row['fo_gde_wappen'];
                $p1 = $pict_path . $row['fo_gde_wappen'];
                $row['fo_gde_wappen'] = "<a href='$p1' target='Ortswappen' > <img src='$p1' alter='$p1' width='70px'>  Groß </a>";
            }
        } elseif ($tabelle == "aw_ff_wappen") {
            # echo "L 107: fh_ff_wappen \$tabelle $tabelle <br/>";
            $fo_id = $row['fo_id'];
            $row['fo_id'] = "<a href='VF_PS_OV_FW_Edit.php?ID=$fo_id' >" . $fo_id . "</a>";

            $pict_path = "AOrd_Verz/PSA/AERM/Wappen_FW/";

            if ($row['fo_ff_wappen'] != "") {

                $fo_ff_wappen = $row['fo_ff_wappen'];
                $p1 = $pict_path . $row['fo_ff_wappen'];
                $row['fo_ff_wappen'] = "<a href='$p1' target='Wappen Feuerwehr' > <img src='$p1' alter='$p1' width='70px'>  Groß </a>";
            }
        } elseif ($tabelle == "aw_aermel_abz") {
            # echo "L 121: fh_ff_abz \$tabelle $tabelle <br/>";
            $fo_id = $row['fo_id'];
            $row['fo_id'] = "<a href='VF_PS_OV_AB_Edit.php?ID=$fo_id' >" . $fo_id . "</a>";

            $pict_path = "AOrd_Verz/PSA/AERM/Aermel_Abz/";

            if ($row['fo_ff_abzeich'] != "") {

                $fo_ff_abzeich = $row['fo_ff_abzeich'];
                $p1 = $pict_path . $row['fo_ff_abzeich'];
                $row['fo_ff_abzeich'] = "<a href='$p1' target='Ärmelabzeichen' > <img src='$p1' alter='$p1' width='70px'>  Groß </a>";
            }

            $a_typ = $row['fo_ff_a_typ_a'];

            $row['fo_ff_a_typ_a'] = VF_Aermelabz_text[$a_typ];
        } elseif ($tabelle == "aw_aermel_abz") {}
        return True;
    }
    return false;
} # Ende von Function modifyRow

if ($debug) {
    echo "<pre class=debug>OrtsEdit_ph0_inc beendet</pre>";
}
?>