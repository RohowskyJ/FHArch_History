<?php

/**
 * Formular für Mandanten / Eigner / Urheber
 * 
 * @author Josef Rohowsky - neu 2026
 */

use FWHist\Auth\AccessGuard;
use FWHist\Services\FormRendererFlex_Ext;

if ($debug) {
    echo "<pre class=debug>MandEdit_ph0_inc ist gestarted</pre>";
}

$editProtect = !$canEdit;   // MandEdit.php: Seite nur für ADM-MA/ADM-ALLE erreichbar

// FormRendererFlex_Ext: wie FormRendererFlex, dazu die Autocomplete-Bausteine (renderAutoComp*, bei Schreibschutz leer)
$forms = new FormRendererFlex_Ext($meta, 0, $neu, [], $editProtect, $module );

echo "<div class='white'>";

echo "<input type='hidden' name='ei_id' value='" . (int) $neu['ei_id'] . "' />";
echo "<input type='hidden' name='ei_mitglnr' value='" . htmlspecialchars((string) $neu['ei_mitglnr'], ENT_QUOTES, 'UTF-8') . "' />";
if (!$editProtect) {
    echo AccessGuard::csrfField();   // wird in MandEdit.php beim Speichern geprüft
}
# =========================================================================================================
echo $forms->renderHeader('Eigentümer: ');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('ei_id');
if ($neu['ei_id'] == 0) {
    echo $forms->renderTextLikeFieldFlex('ei_mitglnr',15);
} else {
    echo $forms->renderTextLikeFieldFlex('ei_mitglnr');
}

# =========================================================================================================
echo $forms->renderTrenner('Organisations- Daten');
# =========================================================================================================

echo $forms->renderSelectFieldFlex('ei_org_typ',VF_Eig_Org_Typ);

echo $forms->renderTextLikeFieldFlex('ei_org_name',100);

echo $forms->renderTextLikeFieldFlex('ei_staat',2);
echo $forms->renderAutoCompStaat();

echo $forms->renderTextLikeFieldFlex('ei_bdld',3);
echo $forms->renderAutoCompBdld();

echo $forms->renderTextLikeFieldFlex('ei_bezirk',2);

echo $forms->renderTextLikeFieldFlex('ei_fwkz',2);
echo $forms->renderTextLikeFieldFlex('ei_grdgj',10);
echo $forms->renderTextLikeFieldFlex('ei_adresse',35);
echo $forms->renderTextLikeFieldFlex('ei_plz',7);
echo $forms->renderTextLikeFieldFlex('ei_ort',60);
echo $forms->renderTextLikeFieldFlex('ei_tel',45);
echo $forms->renderTextLikeFieldFlex('ei_fax',45);
echo $forms->renderTextLikeFieldFlex('ei_handy',45);
echo $forms->renderTextLikeFieldFlex('ei_email',100);
echo $forms->renderTextLikeFieldFlex('ei_internet',100);
#echo $forms->renderTextLikeFieldFlex('ei_sterbdat',10,'',"type='date'");
echo $forms->renderTextLikeFieldFlex('ei_abgdat',10,'',"type='date'");
echo $forms->renderTextLikeFieldFlex('ei_neueigner',50);
# =========================================================================================================
echo $forms->renderTrenner('Urheber- Daten');
# =======================================================================================================
echo $forms->renderTextLikeFieldFlex('ei_urh_kurzz',15,'Kurzzeichen für Foto- Name');
echo $forms->renderTextLikeFieldFlex('ei_media',15,'A,F,V .. Audio,Foto,Video');
# =========================================================================================================
echo $forms->renderTrenner('Verantwortlicher (Bei Organisaton)');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('ei_kont_name',100);
echo $forms->renderTextLikeFieldFlex('ei_titel',10);
echo $forms->renderTextLikeFieldFlex('ei_vname',35);
echo $forms->renderTextLikeFieldFlex('ei_name',60);
echo $forms->renderTextLikeFieldFlex('ei_dgr',10);

# =========================================================================================================
echo $forms->renderTrenner('Einverständnis zur Veröffentlichung');
# =========================================================================================================
echo $forms->renderSelectFieldFlex('ei_wlpriv', VF_JN);
echo $forms->renderSelectFieldFlex('ei_vopriv', VF_JN);
echo $forms->renderSelectFieldFlex('ei_wlmus', VF_JN);
echo $forms->renderSelectFieldFlex('ei_vomus', VF_JN);
echo $forms->renderSelectFieldFlex('ei_wlinv', VF_JN);
echo $forms->renderSelectFieldFlex('ei_voinv', VF_JN);
echo $forms->renderSelectFieldFlex('ei_voinf', VF_JN);
echo $forms->renderSelectFieldFlex('ei_vofo', VF_JN);
echo $forms->renderSelectFieldFlex('ei_voar', VF_JN);
echo $forms->renderTextLikeFieldFlex('ei_drwvs',10,'',"type='date'");
echo $forms->renderSelectFieldFlex('ei_drneu', VF_JN);

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('ei_changed_id');
echo $forms->renderTextLikeFieldFlex('ei_changed_at');

# =========================================================================================================

if ($canEdit) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='MandantenList.php'>Zurück zur Liste</a></p>";


/** notwendige css und js einfügen */

if (!$editProtect) {
    echo "<script src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Staat.js'></script>";
    echo "<script src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Bdld.js'></script>";
}

if ($debug) {
    echo "<pre class=debug>mandEdit_ph0_inc beendet</pre>";
}
?>