<?php

/**
 * Unterstützerverwaltung, Formular
 * 
 * @author Josef Rohowsky - neu 2020 - mod 2026
 * 
 */

use Fharch\Core\Services\FormRendererFlex;

if ($debug) {
    echo "<pre class=debug>UnterstEdit_ph0.inc.php ist gestarted </pre>";
}

echo "<div class='white'>";

echo "<input type='hidden' name='fu_id' value='" . $neu['fu_id'] . "'>";

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if (!userBerechtigtOK($Zugr) ) {
    $editProtect = true;
    # $readonly = false;
}   

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

echo "<input type='hidden' name='fu_id' value='" . $neu['fu_id'] . "'>";

# =========================================================================================================
echo $forms->renderHeader('Unterstützer- Daten');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fu_id',0,'','','readonly'); // ,'','','readonly'
# =========================================================================================================
echo $forms->renderTrenner('Organisations- Daten');
# =========================================================================================================


echo $forms->renderTextLikeFieldFlex('fu_orgname', 50);

# =========================================================================================================
echo $forms->renderTrenner('Unterstützer-Daten');
# =========================================================================================================

echo $forms->renderSelectFieldFlex('fu_anrede', VF_Anrede);
echo $forms->renderTextLikeFieldFlex('fu_dgr', 10, 'FF Dienstgrad');
echo $forms->renderTextLikeFieldFlex('fu_tit_vor', 50);
echo $forms->renderTextLikeFieldFlex('fu_name', 50);
echo $forms->renderTextLikeFieldFlex('fu_vname', 50);
echo $forms->renderTextLikeFieldFlex('fu_tit_nach', 50);


echo $forms->renderTextLikeFieldFlex('fu_adresse', 50);
echo $forms->renderTextLikeFieldFlex('fu_plz', 7);
echo $forms->renderTextLikeFieldFlex('fu_ort', 50);

echo $forms->renderTextLikeFieldFlex('fu_tel', 50);
echo $forms->renderTextLikeFieldFlex('fu_email', 50);
# echo $forms->renderTextLikeFieldFlex('fu_email_status', 50);

# ========================================================================================================
echo $forms->renderTrenner('Organisatorisches');
# =========================================================================================================

echo $forms->renderSelectFieldFlex('fu_kateg', VF_Unterst);
echo $forms->renderSelectFieldFlex('fu_weihn_post', VF_JN);

echo $forms->renderSelectFieldFlex('fu_zugr', VF_JN);
echo $forms->renderSelectFieldFlex('fu_aktiv', VF_JN);

echo $forms->renderTextLikeFieldFlex('fu_changed_id','0','','','readonly');
echo $forms->renderTextLikeFieldFlex('fu_changed_at','0','','','readonly');

# =========================================================================================================

if (userBerechtigtOK($Zugr)) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}
echo "<p><a href='UnterstList.php'>Zurück zur Liste</a></p>";

if ($debug) {
    echo "<pre class=debug>UnterstEdit_ph0.inc.php beendet</pre>";
}
?>