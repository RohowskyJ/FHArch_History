<?php 

/**
 * Lste der Presse, Wartu, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 *
 */

use FWHist\Services\FormRendererFlex;

$editProtect = !$canEdit;
$readonly = "";

$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige

if ((int)$neu['pr_id'] !== 0) {
    $readonly = 'readonly';
    $newRec = $canEdit;
}

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );
if ($canEdit) {
    echo \FWHist\Auth\AccessGuard::csrfField();
}

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
 echo $forms->renderTrenner('Fotos');  #
  # =========================================================================================================
  echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";
  $pict_path = $systemBasePath."login/AOrd_Verz/Presse/";

 echo "<input type='hidden' name='pr_bild_1' value='".$neu['pr_bild_1']."' >";
 echo "<input type='hidden' name='pr_bild_2' value='".$neu['pr_bild_2']."' >";
 echo "<input type='hidden' name='pr_bild_3' value='".$neu['pr_bild_3']."' >";
 echo "<input type='hidden' name='pr_bild_4' value='".$neu['pr_bild_4']."' >";
 echo "<input type='hidden' name='pr_bild_5' value='".$neu['pr_bild_5']."' >";
 echo "<input type='hidden' name='pr_bild_6' value='".$neu['pr_bild_6']."' >";
 
 echo "<input type='hidden' id='urhNr' value=''>";
 echo "<input type='hidden' id='aOrd' value=''>";
 
 echo "<input type='hidden' id='reSize' value='1754'>";

 echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";
 
 /** Variablen zur Fehlervermeidung */
 $neu['ei_id'] = 9;
 $neu['ad_sg'] = $neu['ad_sub_sg'] = "";
 
 $pictPath = $basePath . "/FWHist/login/AOrd_Verz/Presse/";
 
 $Feldlaenge = "100px";
 
 $pic_cnt = 6;
 
 $i = 1;
 while ($i <= $pic_cnt) {
     $pct_arr[] = array('udir' => $pictPath, 'ko' => '', 'bi' => 'pr_bild_'.$i , 'rb' => '', 'up_err' => '','f1'=>'','f2'=>'');
     $i++;
 }
 
 Upload_Form($pic_cnt, $pct_arr, $newRec);
 

  if ($canEdit) {
      echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
      echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
  }
  
  echo "<p><a href='PresseList.php'>Zurück zur Liste</a></p>";
  
# =========================================================================================================
 
 echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Urheber.js' ></script>";
  
if ($debug) {echo "<pre class=debug>PresseEdit_ph0.inc.php beendet </pre>";}
?>