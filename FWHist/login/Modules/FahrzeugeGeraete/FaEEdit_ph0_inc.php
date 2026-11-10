<?php

/**
 * Formular für MotFahrzeuge Eigentümer
 * 
 * @author Josef Rohowsky - neu 2026
 */

use FWHist\Services\FormRendererFlex;

if ($debug) {
    echo "<pre class=debug>FaEEdit_ph0_inc ist gestarted</pre>";
}
# var_dump($neu);

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

/**Zugriffsteuerung */
if (!userBerechtigtOK($Zugr)) {
    $editProtect = true;
    # $readonly = false;
}

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

/** Werte für Foto Mgmt */
$dataSetAct = "";
if ($neu['fz_eign_id'] == 0) { // Neueingabe
    $hide_area = 0;
} else {
    $hide_area = 1;
    $dataSetAct = "data-active-index='0'";
}
# var_dump($neu);
$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige

if ((int)$neu['fz_id'] !== 0) {
    $readonly = 'readonly';
    $newRec = true;
}
$blindOpen = $newRec ? '' : ' open';

echo "<input type='hidden' id='ei_id' name='ei_id' value='$ei_id'/>";
echo "<input type='hidden' id='fz_id' name='fz_id' value='" . $neu['fz_id'] . "'/>";
echo "<input type='hidden' id='fz_eign_id' name='fz_eign_id' value='" . $neu['fz_eign_id'] . "'/>";
    
# =========================================================================================================
echo $forms->renderHeader('Motorisierte Fahrzeuge von Eigentümer '); //.$_SESSION['Eigner']['eig_name']);
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fz_eign_id');
echo $forms->renderTextLikeFieldFlex('fz_id');

# =========================================================================================================
echo $forms->renderTrenner('Fahrzeug/Geräte- Beschreibung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fz_docbez', 100);

echo $forms->renderTextLikeFieldFlex('fz_zul_dat', 10);

echo $forms->renderTextLikeFieldFlex('fz_zul_end_dat');

echo $forms->renderTextLikeFieldFlex('fz_zuldaten');

echo $forms->renderTextLikeFieldFlex('fz_changed_id',10);
echo $forms->renderTextLikeFieldFlex('fz_changed_at', 20);

# =========================================================================================================


if (!$editProtect) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='FaFEdit.php?fz_id=$fz_id&ei_id=$ei_id'>Zurück zum Fahrzeug</a></p>";

# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>FaFEdit_ph0.inc.php beendet</pre>";
}
?>