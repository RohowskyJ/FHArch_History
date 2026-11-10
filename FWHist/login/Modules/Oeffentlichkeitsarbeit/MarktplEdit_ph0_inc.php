<?php
/**
 * Lister der Anbote / Nachfragen, Wartung, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 */

if ($debug) {
    echo "<pre class=debug>MarktplEdit_ph0.inc.php ist gestarted</pre>";
}

use FWHist\Services\FormRendererFlex;

$editProtect = !$canEdit;
$readonly = "";

$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige

if ((int)$neu['bs_id'] !== 0) {
    $readonly = 'readonly';
    $newRec = true;
}

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );
if ($canEdit) {
    echo \FWHist\Auth\AccessGuard::csrfField();
}

echo "<input type='hidden' id='recId' name='bs_id' value='".$neu['bs_id']."' >";
echo "<input type='hidden' id='recEigner' value='' >";

$today = date("Y-m_d");

echo $forms->renderHeader('Angebot / Nachfrage');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('bs_id');
echo "<input type='hidden'name='bs_id' value='" . $neu['bs_id'] . "' >";
# =========================================================================================================
echo $forms->renderTrenner('Aussendung');
# =========================================================================================================
echo $forms->renderDatePickerFieldFlex('bs_startdatum', '', 'type="date" min="today" required');
echo $forms->renderDatePickerFieldFlex('bs_enddatum', '', "type='date' min='today' required");

echo "<input type='hidden'name='bs_typ' value='" . $neu['bs_typ'] . "' >";
/*
echo $forms->renderRadioFieldFlex('bs_typ', array(
    "B" => "Biete",
    "S" => "Suche"
)); 
*/
echo $forms->renderSelectFieldFlex('bs_typ', array(
    "B" => "Biete",
    "S" => "Suche"
));

echo $forms->renderTextLikeFieldFlex('bs_kurztext', 1024, '', 'required' );
echo $forms->renderTextLikeFieldFlex('bs_text');

echo $forms->renderTextLikeFieldFlex('bs_email_1', 50, '', 'required');

echo $forms->renderTextLikeFieldFlex('bs_email_2', 50);

# =========================================================================================================
echo $forms->renderTrenner('Fotos');  #
# =========================================================================================================
echo "<input type='hidden' name='bs_bild_1' value='" . $neu['bs_bild_1'] . "'>";
echo "<input type='hidden' name='bs_bild_2' value='" . $neu['bs_bild_2'] . "'>";
echo "<input type='hidden' name='bs_bild_3' value='" . $neu['bs_bild_3'] . "'>";    
echo "<input type='hidden' name='bs_bild_4' value='" . $neu['bs_bild_4'] . "'>";

$pictPath = $basePath . "/FWHist/login/AOrd_Verz/Marktplatz/";

echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";

echo "<input type='hidden' id='urhNr' value=''>";
echo "<input type='hidden' id='aOrd' value=''>";

echo "<input type='hidden' id='reSize' value='1754'>";

$Feldlaenge = "100px";

$pic_cnt = 4;

$i = 1;
while ($i <= $pic_cnt) {
    $pct_arr[] = array('udir' => $pictPath, 'ko' => '', 'bi' => 'bs_bild_'.$i , 'rb' => '', 'up_err' => '','f1'=>'','f2'=>'');
    $i++;
}

Upload_Form($pic_cnt, $pct_arr, $newRec);

# =========================================================================================================
echo $forms->renderTrenner('Beschreiber');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('bs_changed_id');
echo $forms->renderTextLikeFieldFlex('bs_changed_at');

if ($canEdit) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>"; 
}

echo "<p><a href='MarktplList.php'>Zurück zur Liste</a></p>";

echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Urheber.js' ></script>";
echo "<script src='" . $basePath . "/FWHist/login/Services/js/fw_multi_select.js' ></script>";

if ($debug) {
    echo "<pre class=debug>MarktplEdit_ph0.php beendet</pre>";
}
?>