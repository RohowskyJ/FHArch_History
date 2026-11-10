<?php

/**
 * Wappen im Feuerwehrgebrauch- Wartung: Formular (Tabelle psw_ort_wappen)
 *
 * @author Josef Rohowsky - neu 2026
 */

use FWHist\Auth\AccessGuard;
use FWHist\Services\FormRendererFlex;

if ($debug) {
    echo "<pre class=debug>FWAEdit_ph0_inc.php ist gestarted</pre>";
}

$editProtect = !$canEdit;

$forms = new FormRendererFlex($meta, 0, $neu, [], $editProtect, $module );

echo "<div class='white'>";

echo "<input type='hidden' name='fo_id' value='" . (int) $neu['fo_id'] . "'>";
echo "<input type='hidden' name='proj' value='" . htmlspecialchars((string) $proj, ENT_QUOTES, 'UTF-8') . "'>";
if (!$editProtect) {
    echo AccessGuard::csrfField();   // wird in FWAEdit.php beim Speichern geprüft
}

# =========================================================================================================
echo $forms->renderHeader('Wappen bei der Feuerwehr');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fo_id',0,'','','readonly');
echo $forms->renderTextLikeFieldFlex('fo_fw_id',0,'','','readonly');

# =========================================================================================================
echo $forms->renderTrenner('Wappen- Daten');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fo_ff_w_sort', 4);

# =========================================================================================================
echo $forms->renderTrenner('Abbild des Wappens');
# =========================================================================================================
if (!empty($neu['fo_ff_wappen'])) {
    $imageFile = basename((string) $neu['fo_ff_wappen']);
    $imageName = htmlspecialchars($imageFile, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $imageUrl = htmlspecialchars($basePath . '/FWHist/login/AOrd_Verz/PSA/AERM/Wappen_FW/' . rawurlencode($imageFile), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo "<p><a href='{$imageUrl}' target='_blank' rel='noopener noreferrer'><img src='{$imageUrl}' alt='{$imageName}' height='150'></a><br>Bilddatei: {$imageName}</p>";
} else {
    echo "<p>Keine Bilddatei vorhanden.</p>";
}

echo $forms->renderTextLikeFieldFlex('fo_ff_w_komm');

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fo_changed_id',0,'','','readonly');
echo $forms->renderTextLikeFieldFlex('fo_changed_at',0,'','','readonly');

# =========================================================================================================

if ($canEdit) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
} else {
    echo "<p>Nur-Lese-Ansicht. Änderungen sind nur mit der Berechtigung ADM-PSA möglich.</p>";
}
echo "<p><a href='OrtsEdit.php?ID=" . (int) $neu['fo_fw_id'] . "&proj=" . rawurlencode((string) $proj) . "'>Zurück zum Ort</a></p>";
echo "</div>";

# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>FWAEdit_ph0_inc.php beendet</pre>";
}
?>
