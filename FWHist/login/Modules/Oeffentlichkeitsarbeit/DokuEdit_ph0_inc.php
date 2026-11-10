<?php

/**
 * Liste der Dokumentationen, Wartun, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 */

if ($debug) {
    echo "<pre class=debug>DokuEdit_ph0_inc.php ist gestarted</pre>";
}

use FWHist\Services\FormRendererFlex_Ext;

$editProtect = !$canEdit;
$readonly = "";

$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige

if ((int)$neu['dk_id'] !== 0) {
    $readonly = 'readonly';
    $newRec = $canEdit;
}

$forms = new FormRendererFlex_Ext($meta, $phase,  $neu, [], $editProtect, $module );
if ($canEdit) {
    echo \FWHist\Auth\AccessGuard::csrfField();
}

# =========================================================================================================
echo $forms->renderHeader('Dokumentationen des Vereines');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('dk_id');
echo "<input type='hidden' name='dk_id' value='" . $neu['dk_id'] . "' >";
# =========================================================================================================
echo $forms->renderTrenner('Dokument');
# =========================================================================================================
$sel = VF_Doku_Art;

echo $forms->renderSelectFieldFlex('dk_thema', $sel);

$sel = VF_Doku_SG;

echo $forms->renderSelectFieldFlex('dk_sg', $sel);

echo $forms->renderTextLikeFieldFlex('dk_titel', 65);
echo $forms->renderTextLikeFieldFlex('dk_author', 65);
echo $forms->renderTextLikeFieldFlex('dk_urspr', 60);

echo $forms->renderTextLikeFieldFlex('dk_path2dsn', 65);

echo "<input type='hidden' name='dk_dsn_1' value='" . $neu['dk_dsn_1'] . "' >";
echo "<input type='hidden' name='dk_dsn_2' value='" . $neu['dk_dsn_2'] . "' >";
echo "<input type='hidden' name='dk_dsn_3' value='" . $neu['dk_dsn_3'] . "' >";
echo "<input type='hidden' name='dk_dsn_4' value='" . $neu['dk_dsn_4'] . "' >";

echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";

/** Variablen zur Fehlervermeidung */
$neu['ei_id'] = 9;
$neu['ad_sg'] = $neu['ad_sub_sg'] = "";

$pictPath = $basePath . "/FWHist/login/AOrd_Verz/Dokumente/" . $neu['dk_path2dsn'] . "/";

$Feldlaenge = "100px";

$pic_cnt = 4;

$i = 1;
while ($i <= $pic_cnt) {
    $pct_arr[] = array('udir' => $pictPath, 'ko' => '', 'bi' => 'dk_dsn_'.$i , 'rb' => '', 'up_err' => '','f1'=>'','f2'=>'');
    $i++;
}

Upload_Form($pic_cnt, $pct_arr, $newRec);

echo $forms->renderTextLikeFieldFlex('dk_url', 65);

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('dk_changed_id');
echo $forms->renderTextLikeFieldFlex('dk_changed_at');

# =========================================================================================================

if ($canEdit) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='DokuList.php'>Zurück zur Liste</a></p>";

# =========================================================================================================

echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Urheber.js' ></script>";

if ($debug) {
    echo "<pre class=debug>DokuEdit_ph0.inc.php beendet</pre>";
}
?>