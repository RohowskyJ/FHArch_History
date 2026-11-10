<?php

/**
 * Formular für Archivalien
 * 
 * @author Josef Rohowsky - neu 2026
 */

use FWHist\Services\FormRendererFlex_Ext;
use FWHist\Services\API\AutoCompleteAPI;

if ($debug) {
    echo "<pre class=debug>ArchEdit_ph0_inc ist gestarted</pre>";
}

$editProtect = $editProtect ?? true;
$readonly = "";
# var_dump($neu);
$forms = new FormRendererFlex_Ext($meta, $phase,  $neu, [], $editProtect, $module );

echo "<div class='white'>";

echo "<input type='hidden' id='recId' name='ad_id' value='".$neu['ad_id']."'/>";
if (!$editProtect) {
    echo \FWHist\Auth\AccessGuard::csrfField();
}

$Error_Msg = "";

$inveignr = $neu['ei_id'];

$pref_eignr_s = "0".$inveignr;   //  Kurz- Variante
$pref_eignr = substr("00000",1,5-strlen($inveignr)).$inveignr;

if (is_null($neu['ad_ao_fortlnr'])) {
    $neu['ad_ao_fortlnr'] = '0';
}

$ad_fl = $neu['ad_ao_fortlnr'];
$pref_invnr_s = "0".$ad_fl;    //  kurz- Variante
$ad_flnr = substr("000000",1,6-strlen($ad_fl)).$ad_fl;
$neu['ArchNr'] = "<b>$pref_eignr-".$neu['ad_sg'].".".$neu['ad_subsg'].".".$neu['ad_lcsg'].".".$neu['ad_lcssg']."-".$ad_flnr." </b> oder <b>V:$pref_eignr_s-".$neu['ad_sg'].".".$neu['ad_subsg'].".".$neu['ad_lcsg'].".".$neu['ad_lcssg']."-$pref_invnr_s  </b>";
$Tabellen_Spalten_COMMENT['ArchNr'] = "Volle oder Kurze Archivnummer '";

echo "<input type='hidden' name='ad_id' value='".$neu['ad_id']."'/>";
echo "<input type='hidden' name='ei_id' id='ei_idr' value='".$neu['ei_id']."'/>";
echo "<input type='hidden' name='ad_ao_fortlnr' value='".$neu['ad_ao_fortlnr']."'/>";
echo "<input type='hidden' name='ad_sg' value='".$neu['ad_sg']."'/>";
echo "<input type='hidden' name='ad_subsg' value='".$neu['ad_subsg']."'/>";
echo "<input type='hidden' name='ad_lcsg' value='".$neu['ad_lcsg']."'/>";
echo "<input type='hidden' name='ad_lcssg' value='".$neu['ad_lcssg']."'/>";

echo "<input type='hidden' name='ad_changed_id' id='ad_changed_id' value='".$neu['ad_changed_id']."'/>";
echo "<input type='hidden' name='ad_changed_at' id='ad_changed_at' value='".$neu['ad_changed_at']."'/>";

# =========================================================================================================
echo $forms->renderHeader("Archivalien");
# =========================================================================================================

echo $forms->renderTrenner($eigent_arr['eig_name'] . ', ' .  $eigent_arr['eig_verant'] );

$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige

if ((int)$neu['ad_id'] !== 0) {
    $readonly = 'readonly';
    $newRec = true;
}

echo $forms->renderTextLikeFieldFlex('ad_id', 15, '', '', $readonly );
echo $forms->renderTextLikeFieldFlex('ei_id', 15, '', '', $readonly );
echo $forms->renderTextLikeFieldFlex('ArchNr', 15, '', '', $readonly );
$readonly = '';

if ($Error_Msg != "") {
    echo "<div class='error'>$Error_Msg</div>";
}
# =========================================================================================================
echo $forms->renderTrenner('Einordnung laut Archivordnung');
# =========================================================================================================

if ($neu['ad_id'] == "0" || $neu['ad_sg'] == '' ) {
    
    echo $forms->renderMultiSelectFieldFlex(
        fieldName: 'ad_archivwert',
        configId: 'ARCHIVORDNUNG',
        apiUrl: '../../../login/Services/API/MultiSelectAPI.php'
        );
    
} else {
    $ad_sg = $neu['ad_sg'];
    $ad_subsg = $neu['ad_subsg'];
    
    $Aro_Def = $Arl_Def = "";
    $Aro_Def = $arch->getArchivordnungText($ad_sg, $ad_subsg);;
    $Arl_Def = $arch->getArchivordnungErweiterung($neu['ad_sg'], $neu['ad_subsg'], $neu['ad_lcsg'], $neu['ad_lcssg'], $neu['ad_lcssg_s0'], $neu['ad_lcssg_s1']);
    
    echo "<tr><td>Eingereiht nach  Archivordnung: </td><td colspan = '5'>$Aro_Def $Arl_Def </td></tr>";
}

# =========================================================================================================
echo $forms->renderTrenner('Archivalien- Beschreibung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('ad_beschreibg');
echo $forms->renderTextLikeFieldFlex('ad_keywords');
echo $forms->renderTextLikeFieldFlex('ad_namen');
echo $forms->renderTextLikeFieldFlex('ad_sammlg');
echo $forms->renderTextLikeFieldFlex('ad_doc_date', 10,'',"type='date'");

echo $forms->renderTextLikeFieldFlex('ad_isbn',15 );

echo $forms->renderSelectFieldFlex('ad_type',Arc_Type);
echo $forms->renderSelectFieldFlex('ad_format',Arc_Format);

# =========================================================================================================
echo $forms->renderTrenner('Dokument(e)');
# =========================================================================================================

echo "<input type='hidden' name='ad_doc_1' value='".$neu['ad_doc_1']."'>";
echo "<input type='hidden' name='ad_doc_2' value='".$neu['ad_doc_2']."'>";
echo "<input type='hidden' name='ad_doc_3' value='".$neu['ad_doc_3']."'>";
echo "<input type='hidden' name='ad_doc_4' value='".$neu['ad_doc_4']."'>";

$pictPath = $basePath . "/FWHist/login/AOrd_Verz/"; 

$pic_cnt = 4;

$i = 1;
while ($i <= $pic_cnt) {
    $pct_arr[] = array('udir' => $pictPath, 'ko' => '', 'bi' => 'ad_doc_'.$i , 'rb' => '', 'up_err' => '','f1'=>'','f2'=>'');
    $i++;
}

Upload_Form($pic_cnt, $pct_arr, $newRec);

echo $forms->renderTextLikeFieldFlex('ad_neueigner',100);

echo "<input type='hidden' name='ad_neueigner' val='".$neu['ad_neueigner']."' >";

echo $forms->AutoCompMandant('toggle', 'mand', 'mand', '', 'mand_id');

# =========================================================================================================
echo $forms->renderTrenner('Werte');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('ad_wert_orig',20 );
echo $forms->renderTextLikeFieldFlex('ad_orig_waehrung', 50);
echo $forms->renderTextLikeFieldFlex('ad_wert_kauf',20);
echo $forms->renderTextLikeFieldFlex('ad_kauf_waehrung',50 );
echo $forms->renderTextLikeFieldFlex('ad_wert_besch',20 );
echo $forms->renderTextLikeFieldFlex('ad_besch_waehrung', 50);
# =========================================================================================================
echo $forms->renderTrenner('Lagerort');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('ad_lagerort',100 );

echo $forms->renderTextLikeFieldFlex('ad_l_raum',50 );
echo $forms->renderTextLikeFieldFlex('ad_l_kasten',50 );
echo $forms->renderTextLikeFieldFlex('ad_l_fach',50 );
echo $forms->renderTextLikeFieldFlex('ad_l_pos_x',50 );
echo $forms->renderTextLikeFieldFlex('ad_l_pos_y',50 );
/* temp aktiv */

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('ad_changed_id');
echo $forms->renderTextLikeFieldFlex('ad_changed_at' );


// Verleihdaten fehlen noch

if ( !$editProtect ) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<a href='ArchivList.php?mand_id=" . $neu['ei_id'] . " ' >Zurück zur Liste</a>";

echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js' ></script>";
echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Urheber.js' ></script>";
echo "<script src='" . $basePath . "/FWHist/login/Services/js/fw_multi_select.js' defer></script>";

?>