<?php
/**
 * Wartung der Ärmelabzeichen bei der Feuerwehr, Formular
 *
 * @author Josef Rohowsky - neu 2020
 *
 */

use FWHist\Auth\AccessGuard;
use FWHist\Services\FormRendererFlex;

if ($debug) {
    echo "<pre class=debug>AermAbzEdit_ph0.php ist gestarted</pre>";
}

$editProtect = !$canEdit;
$readonly = "";

/** Vars für accordion */
$accUpd = 1;

/**Zugriffsteuerung */
if ($editProtect) {
    $accUpd = 0; // für accordion
}

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );


echo "<div class='white'>";

echo "<input type='hidden' name='fo_id' value='" . $neu['fo_id'] . "'>";
if (!$editProtect) {
    echo AccessGuard::csrfField();   // wird in AermAbzEdit.php beim Speichern geprüft
}

# =========================================================================================================
echo $forms->renderHeader('Ärmel- und Hemden- Abzeichen');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fo_id');
echo $forms->renderTextLikeFieldFlex('fo_fw_id');

# =========================================================================================================
echo $forms->renderTrenner('Daten der Ärmel- Abzeichen');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fo_ff_a_sort', 4);

# =========================================================================================================
echo $forms->renderTrenner('Abbild des Wappens');
# =========================================================================================================
if (!empty($neu['fo_ff_abzeich'])) {
    $imageName = htmlspecialchars((string) $neu['fo_ff_abzeich'], ENT_QUOTES, 'UTF-8');
    echo "<p>Vorhandene Bilddatei: {$imageName}</p>";
}
/*
Edit_Show_Pict(Prefix . 'fo_ff_abzeich', # Array index Name in $neu[] und $Tabellen_Spalten_Titel[]
$Feldlaenge = '300px' # Feld Länge für Bildbreite
); # Attribut/Parameter
   # if (empty($neu['fo_gde_w_komment'])) {$neu['fo_gde_w_komment'] = $neu['fo_gde_wappen'];}
*/
echo $forms->renderSelectFieldFlex('fo_ff_a_typ_a', Aermelabz_text, '');

echo $forms->renderTextLikeFieldFlex('fo_ff_abz_typ');

# Edit_Upload_File('fo_ff_abzeich', # Array index Name in $neu[] und $Tabellen_Spalten_Titel[]
#$Ident = '1' # Identifier bei mehreren uploads
#);

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fo_changed_id');
echo $forms->renderTextLikeFieldFlex('fo_changed_at');

# =========================================================================================================

if ($canEdit) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
} else {
    echo "<p>Nur-Lese-Ansicht. Änderungen sind nur mit der Berechtigung ADM-PSA möglich.</p>";
}
echo "<p><a href='OrtsEdit.php?ID=".$neu['fo_fw_id']."'>Zurück zur Liste</a></p>";

# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>AermAbzEdit_ph0_inc.php beendet</pre>";
}
?>