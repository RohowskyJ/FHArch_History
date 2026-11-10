<?php 

/**
 * Benutzervrwaltung, Warten, Formular
 * 
 * @author Josef Rohowsky - neu 2018
 * 
 * 
 */

use FWHist\Auth\AccessGuard;
use FWHist\Services\FormRendererFlex;

if ($debug) {echo "<pre class=debug>BenEdit_ph0_inc.php ist gestarted</pre>";}
/*
$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if (isset($_SESSION['BS_Prim']['BE']) && $_SESSION['BS_Prim']['BE']['roles'] != '' ) {
    # $editProtect = true;
    # $readonly = false;
}   
*/

// Seite nur für ADM-ZUGR/ADM-ALLE erreichbar (BenEdit.php); der Schutz greift, falls die Regel einmal geändert wird
$editProtect = !$guard->canManageAccess();

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );
# var_dump($neu);
echo "<div class='white'>";

echo "<input type='hidden' name='fd_id' value='".$neu['fd_id']."' />";
echo "<input type='hidden' name='be_mi_id' value='".(int) $neu['be_mi_id']."' />";
if (!$editProtect) {
    echo AccessGuard::csrfField();   // wird in BenEdit.php beim Speichern geprüft
}
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
  
  echo $forms->renderDatePickerFieldFlex('fd_geb_dat');
  echo $forms->renderDatePickerFieldFlex('fd_sterb_dat');
  echo $forms->renderDatePickerFieldFlex('fd_gaustr_dat');

  # =========================================================================================================
  echo $forms->renderTrenner('Letzte Änderung');
  # =========================================================================================================
  echo $forms->renderTextLikeFieldFlex('fd_changed_id',0,'','','readonly');
  echo $forms->renderTextLikeFieldFlex('fd_changed_at',0,'','','readonly');
  
# =========================================================================================================

  if (!$editProtect) {
      echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
      echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";

      require 'RollenList_inc.php';
      require 'MandErlList_inc.php';
      if ((int) ($neu['be_id'] ?? 0) >= 1) {   // die Skripte der Listen genau einmal einbinden
          echo "<script src=\"" . htmlspecialchars($basePath . '/FWHist/js/tabulator-6.5.2/tabulator.min.js', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\"></script>";
          echo "<script src=\"" . htmlspecialchars($basePath . '/FWHist/js/list-tables.js', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\"></script>";
      }
  }
   
 echo "<p><a href='BenList.php'>Zurück zur Liste</a></p>";
 
 echo "</div>";
 
 echo "<script type='text/javascript' src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Staat.js' ></script>";
  
# =========================================================================================================
 
if ($debug) {echo "<pre class=debug>BenEdit_ph0_inc.php beendet</pre>";}
?>