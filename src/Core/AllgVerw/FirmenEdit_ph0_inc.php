<?php 
/**
 * Benutzervrwaltung, Warten, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 *
 */

if ($debug) {echo "<pre class=debug>VF_FirmenEdit_ph0.inc.php ist gestarted</pre>";}

use Fharch\Core\Services\FormRendererFlex;

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if (!userBerechtigtOK($Zugr) ) {
     $editProtect = true;
     $readonly = false;
}   

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

echo "<div class='white'>";

echo "<input type='hidden' name='fi_id' value='".$neu['fi_id']."' />";
# =========================================================================================================
echo $forms->renderHeader('Firmen '.$neu['fi_name']." ".$neu['fi_abk']);
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fi_id',0,'','','readonly');
 
# =========================================================================================================
echo $forms->renderTrenner('Firma');
# =========================================================================================================
  echo $forms->renderTextLikeFieldFlex('fi_abk',30);
  echo $forms->renderTextLikeFieldFlex('fi_name',100);
  echo $forms->renderTextLikeFieldFlex('fi_ort',100);
  echo $forms->renderTextLikeFieldFlex('fi_Bereich',200);
  
  echo $forms->renderSelectFieldFlex('fi_funkt', VF_Fahrz_Herst);
  
  echo $forms->renderTextLikeFieldFlex('fi_inet',100);
  
  # =========================================================================================================
  echo $forms->renderTrenner('Historisches');
  # =========================================================================================================
  
  echo $forms->renderTextLikeFieldFlex('fi_beginn',12);
  echo $forms->renderTextLikeFieldFlex('fi_ende',12);
 
  echo $forms->renderTextLikeFieldFlex('fi_nachfolger',300);

  # =========================================================================================================
  echo $forms->renderTrenner('URL Checks');
  # =========================================================================================================
  
  echo $forms->renderTextLikeFieldFlex('fi_url_chkd',12);
  echo $forms->renderTextLikeFieldFlex('fi_url_obsolete',12);
  
  # =========================================================================================================
  echo $forms->renderTrenner('Letzte Änderung');
  # =========================================================================================================
  echo $forms->renderTextLikeFieldFlex('fi_changed_id',0,'','','readonly');
  echo $forms->renderTextLikeFieldFlex('fi_changed_at',0,'','','readonly');
  
# =========================================================================================================

  if (userBerechtigtOK($Zugr)) {
       echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
       echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";
  }
   
  echo "<p><a href='FirmenList.php'>Zurück zur Liste</a></p>";
  
  echo "</div>";    

# =========================================================================================================
 
if ($debug) {echo "<pre class=debug>FirmenEdit_ph0_inc.php beendet</pre>";}
?>