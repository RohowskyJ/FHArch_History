<?php

/**
 * Zeitungs- Wartung,  Formular
 *
 * @author J.Rohowsky
 *
 *
 */

if ($debug) {
    echo "<pre class=debug>ZeitungEdit_ph0.inc.php ist gestarted</pre>";
}

use Fharch\Core\Services\FormRendererFlex;

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if ($Zugr == "Alle" || !userBerechtigtOK($Zugr)) {
    $editProtect = true;
    # $readonly = false;
}
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

echo "<input type='hidden' name='zt_id' value='$zt_id'/>";
echo "<input type='hidden' name='zt_daten' value='" . $neu['zt_daten'] . "'/>";
# =========================================================================================================
echo $forms->renderHeader('Zeitungsdaten');

# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('zt_id');

# =========================================================================================================
echo $forms->renderTrenner('Information über die Zeitung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('zt_name', 100);
echo $forms->renderTextLikeFieldFlex('zt_herausg', 100);
echo $forms->renderTextLikeFieldFlex('zt_internet', 100);
echo $forms->renderTextLikeFieldFlex('zt_email', 100);

echo $forms->renderTextLikeFieldFlex('zt_daten');

echo $forms->renderTextLikeFieldFlex('zt_erstausgdat', 12, 'Datum der Erstausgabe');
echo $forms->renderTextLikeFieldFlex('zt_letztausgabe', 12, 'Datum letzten Ausgabe');

echo $forms->renderTextLikeFieldFlex('zt_changed_id');
echo $forms->renderTextLikeFieldFlex('zt_changed_at');
# =========================================================================================================


if ($Zugr != "Alle" && userBerechtigtOK($Zugr)) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='ZeitungList.php'>Zurück zur Liste</a></p>";

if ($debug) {
    echo "<pre class=debug>ZeitungEdit_ph0_inc.php beendet</pre>";
}
?>