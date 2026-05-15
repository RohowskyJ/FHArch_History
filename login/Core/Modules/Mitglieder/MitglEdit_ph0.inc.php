<?php

/**
 * Mitgliederverwaltung, Formular
 * 
 * @author Josef Rohowsky - neu 2020 - mod 2026
 * 
 */

use Fharch\Core\Services\FormRendererFlex;

if ($debug) {
    echo "<pre class=debug>MitglEdit_ph0.inc.php ist gestarted </pre>";
}

echo "<div class='white'>";

echo "<input type='hidden' name='mi_id' value='" . $neu['mi_id'] . "'>";
echo "<input type='hidden' name='mi_einversterkl' value='" . $neu['mi_einversterkl'] . "'>";
echo "<input type='hidden' name='mi_einv_dat' value='" . $neu['mi_einv_dat'] . "'>";

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if (!userBerechtigtOK($Zugr) ) {
   # $editProtect = true;
    # $readonly = false;
}   
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

# =========================================================================================================
echo $forms->renderHeader('Mitglieder- Daten');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('mi_id',0,'','','readonly'); // ,'','','readonly'
# =========================================================================================================
echo $forms->renderTrenner('Organisations- Daten');
# =========================================================================================================

echo $forms->renderRadioFieldFlex('mi_org_typ', M_Org);
echo $forms->renderTextLikeFieldFlex('mi_org_name', 50);

# =========================================================================================================
echo $forms->renderTrenner('Mitglieds-Daten');
# =========================================================================================================
echo $forms->renderRadioFieldFlex('mi_mtyp', M_Typ);

echo $forms->renderRadioFieldFlex('mi_anrede',["Fr." => "Frau","Hr." => "Herr"]);
echo $forms->renderTextLikeFieldFlex('mi_dgr', 10, 'FF Dienstgrad');
echo $forms->renderTextLikeFieldFlex('mi_titel', 10);
echo $forms->renderTextLikeFieldFlex('mi_name', 50);
echo $forms->renderTextLikeFieldFlex('mi_vname', 50);
echo $forms->renderTextLikeFieldFlex('mi_n_titel', 10);
echo $forms->renderTextLikeFieldFlex('mi_gebtag', 10, '', "type='date'");

# echo "<input type='hidden' name='mi_staat' value='".$neu['mi_staat']." >";
echo $forms->renderTextLikeFieldFlex('mi_staat', 0);   // , $neu['st_name']
AutoCompForm_Staat();

echo $forms->renderTextLikeFieldFlex('mi_anschr', 50);
echo $forms->renderTextLikeFieldFlex('mi_plz', 7);
echo $forms->renderTextLikeFieldFlex('mi_ort', 50);

echo $forms->renderTextLikeFieldFlex('mi_tel_handy', 50);
echo $forms->renderTextLikeFieldFlex('mi_fax', 50);
echo $forms->renderTextLikeFieldFlex('mi_email', 50);
echo $forms->renderTextLikeFieldFlex('mi_email_status', 50);

# ========================================================================================================
echo $forms->renderTrenner('Mitarbeit, Information');
# =========================================================================================================

echo $forms->renderRadioFieldFlex('mi_vorst_funct', V_Funktion);
echo $forms->renderRadioFieldFlex('mi_ref_leit', L_Funktion);

# ==============================================
echo $forms->renderTrenner('Wo sind meine Interessen: ');
# =========================================================================================================

echo $forms->renderCheckboxFieldFlex('mi_ref_int_2', VF_Referate_anmeld[2]);
echo $forms->renderCheckboxFieldFlex('mi_ref_int_3', VF_Referate_anmeld[3]);
echo $forms->renderCheckboxFieldFlex('mi_ref_int_4', VF_Referate_anmeld[4]);

# =========================================================================================================
echo $forms->renderTrenner('Ein- Austritt, Sterbedatum');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('mi_eintrdat', 15, '', "type='date'");
echo $forms->renderTextLikeFieldFlex('mi_austrdat', 15, '', "type='date'");
echo $forms->renderTextLikeFieldFlex('mi_sterbdat', 15, '', "type='date'");

# =========================================================================================================
echo $forms->renderTrenner('Mitgliedsbeitrag, Abo, Ausgabe');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('mi_m_beitr_bez', 50,'','','readonly');
echo $forms->renderTextLikeFieldFlex('mi_m_abo_bez', 50,'','','readonly');

echo $forms->renderTextLikeFieldFlex('mi_abo_ausg', 50);

# =========================================================================================================
echo $forms->renderTrenner('Datenschutz- Erklärung (DSVGO)');
# =========================================================================================================
echo $forms->renderRadioFieldFlex('mi_einv_art', M_Einv);

echo $forms->renderTextLikeFieldFlex('mi_einversterkl','0','','','readonly');
echo $forms->renderTextLikeFieldFlex('mi_einv_dat','0','','','readonly');

echo $forms->renderTextLikeFieldFlex('mi_changed_id','0','','','readonly');
echo $forms->renderTextLikeFieldFlex('mi_changed_at','0','','','readonly');

# =========================================================================================================

if (userBerechtigtOK($Zugr)) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}
echo "<p><a href='MitglList.php'>Zurück zur Liste</a></p>";

echo "<script type='text/javascript' src='" . $path2ROOT . "public/js/AutoComp_Staat.js' ></script>";

echo "<div class='w3-container'><fieldset> <label> Auszeichnungs- Details: </label><br/>";
require 'MitglEhrgList.inc.php';
echo "</fieldset></div>";

# =========================================================================================================
#
#
# =========================================================================================================
function modifyRow(array &$row, # die Werte - das array wird by Name übergeben um die Inhalte ändern zu könnnen !!!!
$tabelle)
{
    global $path2VF, $T_List, $module, $pict_path, $proj;
    # echo "L 86: \$tabelle $tabelle <br/>";

    if ($tabelle == "fh_m_ehrung") {
        # $pict_path = "referat4/AUSZ/".$_SESSION[$proj]['fw_bd_abk']."/";
        $fe_lfnr = $row['fe_lfnr'];
        $row['fe_lfnr'] = "<a href='VF_M_EH_Edit.php?ID=$fe_lfnr' >" . $fe_lfnr . "</a>";
        if ($row['fe_bild1'] != "") {
            $pict_path = "AOrd_Verz/1/MITGL/";
            
            $fe_bild1 = $row['fe_bild1'];
            $p1 = $pict_path . $row['fe_bild1'];
            
            $row['fe_bild1'] = "<a href='$p1' target='Bild 1' > <img src='$p1' alter='$p1' width='70px'>  $fe_bild1  </a>";
        }
    }
    return True;
} # Ende von Function modifyRow

if ($debug) {
    echo "<pre class=debug>MitglEdit_ph0.inc.php beendet</pre>";
}
?>