<?php 
/**
 * Benutzervrwaltung, Warten, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 *
 */

if ($debug) {echo "<pre class=debug>VF_AbkuerzEdit_ph0.inc.php ist gestarted</pre>";}


use Fharch\Core\Services\FormRendererFlex;

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if (!userBerechtigtOK($Zugr) ) {
    # $editProtect = true;
    # $readonly = false;
}

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

echo "<div class='white'>";

echo "<input type='hidden' name='ab_id' value='".$neu['ab_id']."' />";
# =========================================================================================================
echo $forms->renderHeader('Abkürzung '.$neu['ab_bezeichn']." ".$neu['ab_abk']);
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('ab_id',0,'','','readonly');
 
# =========================================================================================================
echo $forms->renderTrenner('Abkürzung');
# =========================================================================================================
  echo $forms->renderTextLikeFieldFlex('ab_abk',40);
  echo $forms->renderSelectFieldFlex('ab_grp', VF_Abk_Grp); // 
  echo $forms->renderTextLikeFieldFlex('ab_bezeichn',100);
  echo $forms->renderTextLikeFieldFlex('ab_gruppe', 10);
  
  # =========================================================================================================
  echo $forms->renderTrenner('Letzte Änderung');
  # =========================================================================================================
  echo $forms->renderTextLikeFieldFlex('ab_changed_id',0,'','','readonly');
  echo $forms->renderTextLikeFieldFlex('ab_changed_at',0,'','','readonly');
  
# =========================================================================================================

  if (userBerechtigtOK($Zugr) ) {
       echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
       echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";
  }
   
  echo "<p><a href='AbkuerzList.php'>Zurück zur Liste</a></p>";
  
  echo "</div>";    

# =========================================================================================================
 
if ($debug) {echo "<pre class=debug>AbkuerzEdit_ph0_inc.php beendet</pre>";}
?>