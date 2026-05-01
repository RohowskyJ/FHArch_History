<?php 

/**
 * Benutzervrwaltung, Warten, Formular
 * 
 * @author Josef Rohowsky - neu 2018
 * 
 * 
 */

use Fharch\Core\Services\FormRendererFlex;

/**
 * Includes-Liste
 * enthält alle jeweils includierten Scritpt Files
 */
$_SESSION[$module]['Inc_Arr'][] = "BenEdit_ph0_inc.php";

if ($debug) {echo "<pre class=debug>BenEdit_ph0_inc.php ist gestarted</pre>";}

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";
/*
if (isset($_SESSION['BS_Prim']['BE']) && $_SESSION['BS_Prim']['BE']['roles'] != '' ) {
    # $editProtect = true;
    # $readonly = false;
}   
*/
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );
# var_dump($neu);
echo "<div class='white'>";

echo "<input type='hidden' name='fd_id' value='".$neu['fd_id']."' />";
echo "<input type='hidden' name='be_mi_id' value='".$neu['be_mi_id']."' />";
# =========================================================================================================
echo $forms->renderHeader('Benutzer '.$neu['fd_vname']." ".$neu['fd_name']);
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fd_id',0,'','','readonly');
echo $forms->renderTextLikeFieldFlex('be_id',0,'','','readonly');
echo $forms->renderTextLikeFieldFlex('be_mi_id',0,'','','readonly');
 
  # =========================================================================================================
  echo $forms->renderTrenner('Benutzer');
  # =========================================================================================================
  echo $forms->renderTextLikeFieldFlex('fd_anrede',15);
  echo $forms->renderTextLikeFieldFlex('fd_tit_vor',15);
  echo $forms->renderTextLikeFieldFlex('fd_vname',35);
  echo $forms->renderTextLikeFieldFlex('fd_name',35);
  echo $forms->renderTextLikeFieldFlex('fd_tit_nach',15);
  echo $forms->renderTextLikeFieldFlex('fd_adresse',35);
  echo $forms->renderTextLikeFieldFlex('fd_plz',7);
  echo $forms->renderTextLikeFieldFlex('fd_ort',60);
 
  echo $forms->renderTextLikeFieldFlex('fd_staat_abk', 0);   // , $neu['st_name']
  AutoCompForm_Staat();
  
  echo $forms->renderTextLikeFieldFlex('fd_tel',100);
  echo $forms->renderTextLikeFieldFlex('fd_email',100);
  echo $forms->renderTextLikeFieldFlex('fd_email_status',10);
  echo $forms->renderTextLikeFieldFlex('fd_hp',100);
  
  echo $forms->renderTextLikeFieldFlex('fd_geb_dat',12);
  echo $forms->renderTextLikeFieldFlex('fd_sterb_dat',12);
  echo $forms->renderTextLikeFieldFlex('fd_austr_dat',12);
  
  # =========================================================================================================
  echo $forms->renderTrenner('Letzte Änderung');
  # =========================================================================================================
  echo $forms->renderTextLikeFieldFlex('fd_changed_id',0,'','','readonly');
  echo $forms->renderTextLikeFieldFlex('fd_changed_at',0,'','','readonly');
  
# =========================================================================================================

  if (isset($_SESSION['BS_Prim']['BE']) && $_SESSION['BS_Prim']['BE']['roles'] == 'ADM-ALLE') {
  
          echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
          echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";
          
          require 'RollenList_inc.php';
 }
   
 echo "<p><a href='BenList.php'>Zurück zur Liste</a></p>";
 
 echo "</div>";
 
 echo "<script type='text/javascript' src='" . $path2ROOT . "login/common/javascript/FS_AutoComp_Staat.js' ></script>";
  
# =========================================================================================================
 
if ($debug) {echo "<pre class=debug>BenEdit_ph0_inc.php beendet</pre>";}
?>