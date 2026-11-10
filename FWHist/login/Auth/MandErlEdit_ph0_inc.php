<?php

/**
 * Mandanten-Erlaubnis zu einem Benutzer, Formular
 *
 * @author Josef Rohowsky - neu 2018
 */

use FWHist\Auth\AccessGuard;
use FWHist\Services\FormRendererFlex;

if ($debug) {echo "<pre class=debug>MandErlEdit_ph0_inc.php ist gestarted</pre>";}

// Seite nur für ADM-ZUGR/ADM-ALLE erreichbar (MandErlEdit.php); der Schutz greift, falls die Regel einmal geändert wird
$editProtect = !$guard->canManageAccess();

// Auswahl: alle Mandanten ($mandanten in MandErlEdit.php: [ei_id => Name])
$mandSel = ['' => ' - - - Einen Wert Auswählen - - - '];
foreach ($mandanten as $eiId => $eiName) {
    $mandSel[$eiId] = htmlspecialchars($eiName, ENT_QUOTES, 'UTF-8');
}

$neu['Mandant'] = $mandSel[$neu['ei_id']] ?? '';

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );
echo "<input type='hidden' name='fm_id' value='" . (int) $neu['fm_id'] . "' />";
echo "<input type='hidden' name='be_id' value='" . (int) $neu['be_id'] . "' />";
echo "<input type='hidden' name='ei_id' value='" . (int) $neu['ei_id'] . "' />";
if (!$editProtect) {
    echo AccessGuard::csrfField();   // wird in MandErlEdit.php beim Speichern geprüft
}
# =========================================================================================================
echo $forms->renderHeader('Benutzer ' . $benName);
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fm_id',0,'','','readonly');

# =========================================================================================================
echo $forms->renderTrenner('Mandanten- Erlaubnis');
# =========================================================================================================
if ($neu['fm_aktiv'] == '' ) {$neu['fm_aktiv'] = 'a';}
echo $forms->renderRadioFieldFlex('fm_aktiv',['a' => 'Aktiv', 'i' => 'Inaktiv']);

echo $forms->renderTextLikeFieldFlex('Mandant',100,'','','readonly');

echo $forms->renderSelectFieldFlex('descript', $mandSel, '' , '', '','Mandant');

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('fm_changed_id',0,'','','readonly');
echo $forms->renderTextLikeFieldFlex('fm_changed_at',0,'','','readonly');

# =========================================================================================================

if (!$editProtect) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";
}

echo "<p><a href='BenEdit.php?ID=" . urlencode((string) $profileId) . "'>Zurück zum Benutzer</a></p>";

echo "</div>";
# =========================================================================================================

if ($debug) {echo "<pre class=debug>MandErlEdit_ph0_inc.php beendet</pre>";}
?>
