<?php

/**
 * Formular für Medien verzeichnisse
 * 
 * @author Josef Rohowsky - neu 2026
 */
use FWHist\Services\FormRendererFlex;

if ($debug) {
    echo "<pre class=debug>MedVEdit_ph0_inc ist gestarted</pre>";
}

$editProtect = !$canEdit;
$readonly = "";

/**
 * Zugriffsteuerung
 */
#var_dump($meta);
$forms = new FormRendererFlex($meta, $phase, $neu, [], $editProtect, $module);
if ($canEdit) {
    echo \FWHist\Auth\AccessGuard::csrfField();
}

echo "<input type='hidden' name='md_id' value='" . $neu['md_id'] . "'/>";
echo "<input type='hidden' id='urhNr' name='ei_id' value='" . $neu['ei_id'] . "'/>";
echo "<input type='hidden' name='md_Urheber' value='" . $neu['md_Urheber'] . "'/>";
echo "<input type='hidden' name='md_dsn_1' value='" . $neu['md_dsn_1'] . "'/>";
echo "<input type='hidden' name='md_namen' value='" . $neu['md_namen'] . "'/>";
echo "<input type='hidden' name='md_sammlg' value='" . $neu['md_sammlg'] . "'/>";
echo "<input type='hidden' name='md_feuerwehr' value='" . $neu['md_feuerwehr'] . "'/>";
echo "<input type='hidden' name='md_fw_id' value='" . $neu['md_fw_id'] . "'/>";
echo "<input type='hidden' name='md_changed_id' value='" . $neu['md_changed_id'] . "'/>";
echo "<input type='hidden' name='md_changed_at' value='" . $neu['md_changed_at'] . "'/>";

if (! empty($Err_msg)) {
    echo "<span class='error'>Eingabe Fehler,bitte korrigieren.</span>";
}

# =========================================================================================================
echo $forms->renderHeader('Audio-, Foto-, Video Beschreibung');
# =========================================================================================================

# echo $forms->renderTrenner($eigent_arr['eig_name'] . ', ' .  $eigent_arr['eig_verant'] );

echo $forms->renderTextLikeFieldFlex('md_id', 0, '', '', 'readonly' );
echo $forms->renderTextLikeFieldFlex('ei_id', 0, '', '', 'readonly' );
echo $forms->renderTextLikeFieldFlex('md_Urheber', 0, '', '', 'readonly' );

# =========================================================================================================
echo $forms->renderTrenner('Daten');
# =========================================================================================================

echo $forms->renderSelectFieldFlex('md_media', ['Audio' => 'Audio- Daten', 'Foto' => 'Fotos', 'Video' => 'Videos']);

echo $forms->renderTextLikeFieldFlex('md_aufn_datum', 15,  '', '', 'readonly' );

echo $forms->renderTextLikeFieldFlex('md_beschreibg', 1024);

echo $forms->renderTextLikeFieldFlex( 'md_suchbegr', '1024', 'Suchbegriffe'); 

echo $forms->renderTextLikeFieldFlex( 'md_Med_anz', 0, '', '', 'readonly' ); 

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('md_changed_id', 0, '', '', 'readonly' );
echo $forms->renderTextLikeFieldFlex('md_changed_at', 0, '', '', 'readonly' );

# =========================================================================================================

if ( !$editProtect ) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='MedienVerzList.php?urh_id=" . $neu['ei_id'] . "'>Zurück zur Liste</a></p>";

# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>MedVEdit_ph0.inc.php beendet</pre>";
}


?>