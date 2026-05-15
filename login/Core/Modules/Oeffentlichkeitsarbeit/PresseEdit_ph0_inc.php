<?php 

/**
 * Lste der Presse, Wartu, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 *
 */

use Fharch\Core\Services\FormRendererFlex;

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if ($Zugr == "Alle" || !userBerechtigtOK($Zugr)) {
    $editProtect = true;
    # $readonly = false;
}
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

echo "<input type='hidden' id='recId' name='pr_id' value='".$neu['pr_id']."' >";
echo "<input type='hidden' id='recEigner'  value='r' >";

# =========================================================================================================
echo $forms->renderHeader('Pressebericht ');
# =========================================================================================================
  
  echo $forms->renderTextLikeFieldFlex('pr_id');
  echo "<input type='hidden' name='pr_id' value='$pr_id' >";
  # =========================================================================================================
 echo $forms->renderTrenner('Aussendung');
  # =========================================================================================================
    
  echo $forms->renderTextLikeFieldFlex( 'pr_datum',10,'',"type='date'");
  echo $forms->renderTextLikeFieldFlex( 'pr_name',50);
  echo $forms->renderTextLikeFieldFlex( 'pr_ausg',10);
  echo $forms->renderRadioFieldFlex( 'pr_medium',array ("PR"=>"Druck","TV"=>"Fensehen, Internet"));
  echo $forms->renderTextLikeFieldFlex( 'pr_seite',10);

  echo $forms->renderTextLikeFieldFlex( 'pr_teaser');
  echo $forms->renderTextLikeFieldFlex( 'pr_text');
 
  # =========================================================================================================
 echo $forms->renderTrenner('Bewertung');
 # =========================================================================================================echo $forms->renderTextLikeFieldFlex(
  echo $forms->renderTextLikeFieldFlex( 'pr_web_site',100);
  echo $forms->renderTextLikeFieldFlex( 'pr_web_text',50);
  echo $forms->renderTextLikeFieldFlex( 'pr_inet',100);
  
  # =========================================================================================================
 echo $forms->renderTrenner('Beschreiber');
  # =========================================================================================================
 
  echo $forms->renderTextLikeFieldFlex('pr_changed_id');
  echo $forms->renderTextLikeFieldFlex('pr_changed_at');
  
  # =========================================================================================================
  $checkbox_f = "";
  if ($Zugr == "Alle" || !userBerechtigtOK($Zugr)) {
      $checkbox_f = "<a href='#' class='toggle-string' data-toggle-group='1'>Foto Daten eingeben/ändern</a>";
  }
 echo $forms->renderTrenner('Fotos',$checkbox_f);  #
  # =========================================================================================================
  echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";
  $pict_path = $path2ROOT."login/AOrd_Verz/Presse/";

 echo "<input type='hidden' name='pr_bild_1' value='".$neu['pr_bild_1']."' >";
 echo "<input type='hidden' name='pr_bild_2' value='".$neu['pr_bild_2']."' >";
 echo "<input type='hidden' name='pr_bild_3' value='".$neu['pr_bild_3']."' >";
 echo "<input type='hidden' name='pr_bild_4' value='".$neu['pr_bild_4']."' >";
 echo "<input type='hidden' name='pr_bild_5' value='".$neu['pr_bild_5']."' >";
 echo "<input type='hidden' name='pr_bild_6' value='".$neu['pr_bild_6']."' >";
 
 echo "<input type='hidden' id='urhNr' value=''>";
 echo "<input type='hidden' id='aOrd' value=''>";
 
 echo "<input type='hidden' id='reSize' value='1754'>";
 
 $Feldlaenge = "100px";
 
 $_SESSION[$module]['Pct_Arr' ] = array();
 $num_foto = 6;
 $i = 1;
 while ($i <= $num_foto) {
     $_SESSION[$module]['Pct_Arr' ][] = array('udir' => $pict_path, 'ko' => '', 'bi' => 'pr_bild_'.$i, 'rb' => '', 'up_err' => '','f1' => '','f2' => '');
     
     echo "<input type='hidden' id='aOrd_$i' value='Presse/'>";
     $i++;
 }
 
UploadForm_M();


  if ($Zugr != "Alle" && userBerechtigtOK($Zugr)) {
      echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
      echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
  }
  
  echo "<p><a href='PresseList.php'>Zurück zur Liste</a></p>";
  
# =========================================================================================================
 
if ($debug) {echo "<pre class=debug>PresseEdit_ph0.inc.php beendet </pre>";}
?>