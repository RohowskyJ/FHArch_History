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

use Fharch\Core\Services\FormRendererFlex;

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if ($Zugr == "Alle" || !userBerechtigtOK($Zugr)) {
    $editProtect = true;
    # $readonly = false;
}
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

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

echo "<input type='hidden' name='dk_Dsn' value='" . $neu['dk_dsn'] . "' >";
echo "<input type='hidden' name='dk_Dsn_2' value='" . $neu['dk_dsn_2'] . "' >";

echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";
$pict_path = "AOrd_Verz/Downloads/";
if ($neu['dk_path2dsn'] != "") {
    $pict_path .= $neu['dk_path2dsn']."";
}

$Feldlaenge = "100px";

$pic_arr = array(
    "1" => "|||dk_dsn",
    "2" => "|||dk_dsn_2"
);
Multi_Foto($pic_arr);

echo $forms->renderTextLikeFieldFlex('dk_path2dsn', 65);

echo $forms->renderTextLikeFieldFlex('dk_url', 65);

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('dk_changed_id');
echo $forms->renderTextLikeFieldFlex('dk_changed_at');

# =========================================================================================================

if ($Zugr != "Alle" && userBerechtigtOK($Zugr)) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='DokuList.php'>Zurück zur Liste</a></p>";

# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>DokuEdit_ph0.inc.php beendet</pre>";
}
?>