<?php

/**
 * Formular für Inventerdaten
 * 
 * @author Josef Rohowsky - neu 2026
 */

use FWHist\Auth\AccessGuard;
use FWHist\Services\FormRendererFlex_Ext;

if ($debug) {
    echo "<pre class=debug>InvEdit_ph0_inc ist gestarted</pre>";
}

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige
/**Zugriffsteuerung */
if (!$guard->canWriteMandantData('INV', (int) $neu['ei_id'])) {
    $editProtect = true;
    # $readonly = false;
    $accUpd = 0; // für accordion
    $newRec = true;
}

$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige

if ((int)$neu['in_id'] !== 0) {
    $readonly = 'readonly';
    $newRec = true;
}

$forms = new FormRendererFlex_Ext($meta, $phase,  $neu, [], $editProtect, $module );

echo "<input type='hidden' name='in_id' value='" . $neu['in_id'] . "'/>";
echo "<input type='hidden' name='ei_id' value='" . $neu['ei_id'] . "'/>";
echo "<input type='hidden' name='in_sammlg' value='" . $neu['in_sammlg'] . "'/>";
echo "<input type='hidden' name='in_linkerkl' value='" . $neu['in_linkerkl'] . "'/>";
if (!$editProtect) {
    echo AccessGuard::csrfField();   // wird in InvEdit.php beim Speichern geprüft
}

$inveignr = $neu['ei_id'];
$pref_eignr_s = "0" . $inveignr; // Kurz- Variante

$pref_eignr = substr("00000", 1, 5 - strlen($inveignr)) . $inveignr;
$in_id = $neu['in_id'];
$pref_invnr_s = "0" . $in_id; // kurz- Variante
$in_flnr = substr("000000", 1, 6 - strlen($in_id)) . $in_id;
//$neu['InvNr'] = "<b>V$pref_eignr" . $_SESSION[$module]['sammlung'] . $in_flnr . " </b> oder <b>V:$pref_eignr_s" . $_SESSION[$module]['sammlung'] . ":$pref_invnr_s </b>";
$Tabellen_Spalten_COMMENT['InvNr'] = "Volle oder Kurze Inventarnummer '";

# =========================================================================================================
echo $forms->renderHeader("Inventar für ".$eigent_arr['eig_name'] . ', ' .  $eigent_arr['eig_verant']);
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('in_id');
echo $forms->renderTextLikeFieldFlex('ei_id');
echo $forms->renderTextLikeFieldFlex('InvNr');
echo "Sammlungs - Auswahl fehlt noch<br>";

if ($neu['in_id'] == 0) {
    # =========================================================================================================
    echo $forms->renderTrenner('Sammlung');
    # =========================================================================================================
    echo $forms->renderTextLikeFieldFlex('InvNr');
    
    echo $forms->renderTextLikeFieldFlex('in_sammlg');
    echo $forms->renderTextLikeFieldFlex('sa_name',);
    # echo "</div>";
    
    echo "<div class='w3-container '> ";
    
    echo "<input type='hidden' name='in_sammlg' value='".$neu['in_sammlg']."'/>";
    
    echo $forms->renderMultiSelectFieldFlex(
        fieldName: 'in_sammlg',
        configId: 'SAMMLUNG',
        apiUrl: '../../../login/Services/API/MultiSelectAPI.php'
        );
    
} else {
    echo $forms->renderTextLikeFieldFlex('in_sammlg');
    echo $forms->renderTextLikeFieldFlex('sa_name');
}

echo "</div>";
# =========================================================================================================
echo $forms->renderTrenner('Inventar- Beschreibung');
# =========================================================================================================
# var_dump($Tabellen_Spalten_COMMENT);
# var_dump($Tabellen_Spalten_MAXLENGTH);
echo $forms->renderTextLikeFieldFlex('in_bezeichnung', 60);
echo $forms->renderTextLikeFieldFlex('in_kommentar');

$pict_path = "AOrd_Verz/" . $neu['ei_id'] . "/INV/";

echo $forms->renderTextLikeFieldFlex('in_sammlg');
/*
if ($neu['in_sammlg'] != "") {
    $pict_path .= $neu['in_sammlg'] . "/";
    $Opt_Det = VF_Sel_Det($neu['in_sammlg'], $neu['in_linkerkl'], 8);
    echo $forms->renderSelectFieldFlex('in_linkerkl', $Opt_Det);
}
*/
echo $forms->renderTextLikeFieldFlex('in_entstehungszeit', 10);
echo $forms->renderSelectFieldFlex('in_epoche', VF_Epoche);

echo $forms->renderTextLikeFieldFlex('in_hersteller', 60);
/*
 $ST_Opt_A = VF_Sel_Staat('in_herstld', '9');
 echo $forms->renderSelectFieldFlex('in_herstld', $ST_Opt_A);
 
 $ST_Opt_A = VF_Sel_Staat('in_aufbld_1', '9');
 echo $forms->renderSelectFieldFlex('in_aufbld_1', $ST_Opt_A);
 # echo $forms->renderTextLikeFieldFlex('in_aufbld_1',10);
 */
echo $forms->renderTextLikeFieldFlex('in_wert_neu', 30);

echo $forms->renderTextLikeFieldFlex('in_neu_waehrg', 50);
echo $forms->renderTextLikeFieldFlex('in_wert_kauf', 30);
echo $forms->renderTextLikeFieldFlex('in_kauf_waehrung', 50);
echo $forms->renderTextLikeFieldFlex('in_wert_besch', 30);
echo $forms->renderTextLikeFieldFlex('in_besch_waehrung', 50);
echo $forms->renderTextLikeFieldFlex('in_zustand');

echo $forms->renderTextLikeFieldFlex('in_namen'); // ,'',"Namen, durch Beistrich <b><q>,</q></b> getrennt");

echo $forms->renderTextLikeFieldFlex('in_abmess', 50, " Abmessungen:  l x b xh in mm");

echo $forms->renderTextLikeFieldFlex('in_gewicht', 50, " in Kg");

echo "<input type='hidden' name='in_vwlinks' value='" . $neu['in_vwlinks'] . "'/>";
echo "<input type='hidden' name='in_beschreibung' value='" . $neu['in_beschreibung'] . "'/>";
echo "<input type='hidden' name='in_refindex' value='" . $neu['in_refindex'] . "'/>";

# =========================================================================================================
echo $forms->renderTrenner('Fotos');  #
# =========================================================================================================
echo "<div>";

echo "<input type='hidden' name='in_foto_1' value='" . $neu['in_foto_1'] . "'>";
echo "<input type='hidden' name='in_foto_2' value='" . $neu['in_foto_2'] . "'>";
echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";

echo "<input type='hidden' id='sammlung' value='".$neu['in_sammlg'] ."'>";
echo "<input type='hidden' id='eigner' value='".$neu['ei_id'] ."'>";
echo "<input type='hidden' id='urhNr' value=''>";

$pictPath = $basePath . "/FWHist/login/AOrd_Verz/";

$pic_cnt = 2;

$i = 1;
while ($i <= $pic_cnt) {
    $pct_arr[] = array('udir' => $pictPath, 'ko' => '', 'bi' => 'in_foto_'.$i , 'rb' => '', 'up_err' => '','f1'=>'','f2'=>'');
    $i++;
}

Upload_Form($pic_cnt, $pct_arr, $newRec);

echo "</div>";

# =========================================================================================================
echo $forms->renderTrenner('Archiv- Einteilung (Bestandsdaten)');
# =========================================================================================================
# echo $forms->renderTextLikeFieldFlex('in_altbestand',10);// VF_JN
echo $forms->renderSelectFieldFlex('in_altbestand', VF_JN, '');
echo $forms->renderTextLikeFieldFlex('in_invjahr', 10);
echo $forms->renderTextLikeFieldFlex('in_eingbuchnr', 15);
echo $forms->renderTextLikeFieldFlex('in_eingbuchdat', 10);
echo $forms->renderTextLikeFieldFlex('in_erstdat', 10);
echo $forms->renderTextLikeFieldFlex('in_ausgdat', 10);


echo $forms->renderTextLikeFieldFlex('in_neueigner',100);

echo "<input type='hidden' name='in_neueigner' val='".$neu['in_neueigner']."' >";

echo $forms->AutoCompMandant('toggle', 'mand', 'mand', '', 'mand_id');

# =========================================================================================================
echo $forms->renderTrenner('Lagerort');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('in_raum', 40);
echo $forms->renderTextLikeFieldFlex('in_platz', 40);

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('in_changed_id');
echo $forms->renderTextLikeFieldFlex('in_changed_at');
# =========================================================================================================

// Verleihdaten fehlen noch

if ( !$editProtect ) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='InventarList.php?mand_id=" . $neu['ei_id'] . "'>Zurück zur Liste</a></p>";
echo "</div>";
echo "</div>";

echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js' ></script>";
echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Urheber.js' ></script>";
echo "<script src='" . $basePath . "/FWHist/login/Services/js/fw_multi_select.js' defer></script>";



?>