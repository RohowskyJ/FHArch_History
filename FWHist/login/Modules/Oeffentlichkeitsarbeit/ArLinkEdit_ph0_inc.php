<?php 

/**
 * Wartung der Archive und Bibliotheken, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 */

use FWHist\Services\FormRendererFlex;

if ($debug) {echo "<pre class=debug>ArLinkEdit_ph0_inc.php ist gestarted</pre>";}

$editProtect = !$canEdit;
$readonly = "";

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );
if ($canEdit) {
    echo \FWHist\Auth\AccessGuard::csrfField();
}

# =========================================================================================================
echo $forms->renderHeader('Bibliotheks- und Archiv- Links');
# =========================================================================================================

echo "<input type='hidden' name='fa_id' value='".$neu['fa_id']."' >";

echo $forms->renderTextLikeFieldFlex('fa_id','0','','','readonly');

# =========================================================================================================
echo $forms->renderTrenner('Link- Daten');
# =========================================================================================================
    
echo $forms->renderTextLikeFieldFlex('fa_link',50);
echo $forms->renderTextLikeFieldFlex('fa_text',60);

# =========================================================================================================

if ($canEdit) {
          echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
          echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}
      
echo "<p><a href='ArLinkList.php'>Zurück zur Liste</a></p>";
# =========================================================================================================
 
if ($debug) {echo "<pre class=debug>ArLinkEdit_ph0_inc.php beendet</pre>";}
?>