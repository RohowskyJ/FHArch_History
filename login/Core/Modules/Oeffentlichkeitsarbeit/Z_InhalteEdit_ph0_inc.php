<?php

/**
 * Zeitungs- Index Liste, Wartung, Formular
 *
 * @author J.Rohowsky
 *
 *
 */

if ($debug) {
    echo "<pre class=debug>VZ_InhalteEdit_ph0.inc.php ist gestarted</pre>";
}

use Fharch\Core\Services\FormRendererFlex;

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if ($Zugr == "Alle" || !userBerechtigtOK($Zugr)) {
    $editProtect = true;
    # $readonly = false;
}
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

echo "<input type='hidden' name='ih_id' value='" . $neu['ih_id'] . "'/>";
echo "<input type='hidden' name='ih_zt_id' value='" . $neu['ih_zt_id'] . "'/>";
# =========================================================================================================
echo $forms->renderHeader('Inhalt');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('ih_id');
echo $forms->renderTextLikeFieldFlex('ih_zt_id');
# echo $forms->renderTextLikeFieldFlex('ge_invnr');
# =========================================================================================================
echo $forms->renderTrenner('Inhalts- Beschreibung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('ih_jahrgang', 4);
echo $forms->renderTextLikeFieldFlex('ih_jahr', 4);
echo $forms->renderTextLikeFieldFlex('ih_nr', 4);

echo $forms->renderSelectFieldFlex('ih_kateg', VF_ZT_Kategorie, '');

echo $forms->renderSelectFieldFlex('ih_sg', VF_ZT_Sachgeb, '');
echo $forms->renderSelectFieldFlex('ih_ssg', VF_ZT_Sub_Sachg, '');

echo $forms->renderTextLikeFieldFlex('ih_gruppe', 30);
echo $forms->renderTextLikeFieldFlex('ih_titel',70);
echo $forms->renderTextLikeFieldFlex('ih_titelerw',70);
echo $forms->renderTextLikeFieldFlex('ih_autor',70);
echo $forms->renderTextLikeFieldFlex('ih_email',60);

echo $forms->renderTextLikeFieldFlex('ih_tel', 60);
echo $forms->renderTextLikeFieldFlex('ih_fax', 60);
echo $forms->renderTextLikeFieldFlex('ih_seite', 4);
echo $forms->renderTextLikeFieldFlex('ih_spalte', 4);

echo $forms->renderTextLikeFieldFlex('ih_fwehr');
# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('ih_changed_id');
echo $forms->renderTextLikeFieldFlex('ih_changed_at');
# =========================================================================================================


if ($Zugr != "Alle" && userBerechtigtOK($Zugr)) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='Z_InhalteList.php?ID=".$neu['ih_zt_id']."'>Zurück zur Liste</a></p>";

if ($debug) {
    echo "<pre class=debug>VF_O_ZT_I_Edit_ph0.inc.php beendet</pre>";
}
?>