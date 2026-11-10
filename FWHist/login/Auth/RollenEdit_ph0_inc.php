<?php

/**
 * Rollenzuordnung zu einem Benutzer, Formular
 *
 * @author Josef Rohowsky - neu 2018
 */

use FWHist\Auth\AccessGuard;
use FWHist\Services\FormRendererFlex;

if ($debug) {echo "<pre class=debug>RollenEdit_ph0_inc.php ist gestarted</pre>";}

// Seite nur für ADM-ZUGR/ADM-ALLE erreichbar (RollenEdit.php); der Schutz greift, falls die Regel einmal geändert wird
$editProtect = !$guard->canManageAccess();

// Auswahl: nur Rollen, die vergeben werden dürfen ($roleDes ist in RollenEdit.php bereits gefiltert)
$roleSel = ['' => ' - - - Einen Wert Auswählen - - - '];
foreach ($roleDes as $role) {
    $roleSel[$role['fl_id']] = htmlspecialchars((string) $role['fl_beschreibung'], ENT_QUOTES, 'UTF-8');
}

$neu['Rolle'] = $roleSel[$neu['fl_id']] ?? '';

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );
$fr_id = $neu['fr_id'];
echo "<input type='hidden' name='fr_id' value='" . (int) $fr_id . "' />";
echo "<input type='hidden' name='be_id' value='" . (int) $neu['be_id'] . "' />";
echo "<input type='hidden' name='fl_id' value='" . (int) $neu['fl_id'] . "' />";
if (!$editProtect) {
    echo AccessGuard::csrfField();   // wird in RollenEdit.php beim Speichern geprüft
}
# =========================================================================================================
echo $forms->renderHeader('Benutzer ' . $benName);
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fr_id',0,'','','readonly');

# =========================================================================================================
echo $forms->renderTrenner('Benutzer ');
# =========================================================================================================
if ($neu['fr_aktiv'] == '' ) {$neu['fr_aktiv'] = 'a';}
echo $forms->renderRadioFieldFlex('fr_aktiv',['a' => 'Aktiv', 'i' => 'Inaktiv']);

echo $forms->renderTextLikeFieldFlex('Rolle',100,'','','readonly');

echo $forms->renderSelectFieldFlex('descript', $roleSel, '' , '', '','Rollen- Beschreibung') ;

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('fr_changed_id',0,'','','readonly');
echo $forms->renderTextLikeFieldFlex('fr_changed_at',0,'','','readonly');

# =========================================================================================================

if (!$editProtect) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";
}

echo "<p><a href='BenEdit.php?ID=" . urlencode((string) $profileId) . "'>Zurück zum Benutzer</a></p>";

echo "</div>";
# =========================================================================================================

if ($debug) {echo "<pre class=debug>RollenEdit_ph0_inc.php beendet</pre>";}
?>
