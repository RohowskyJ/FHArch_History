<?php 

/**
 * Benutzervrwaltung, Warten, Formular
 * 
 * @author Josef Rohowsky - neu 2018
 * 
 * 
 */

use Fharch\Core\Services\FormRendererFlex;

if ($debug) {echo "<pre class=debug>VS_Rollendit_ph0_inc.php ist gestarted</pre>";}

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if (isset($_SESSION['BS_Prim']['BE']) && $_SESSION['BS_Prim']['BE']['roles'] != '' ) {
    # $editProtect = true;
    # $readonly = false;
}   

$roleDes = $benu->getRoleDescrAll();
$roleSel[''] = ' - - - Einen Wert Auswählen - - - ';
// HTML-Select-Ausgabe

foreach ($roleDes as $role) {
    // Wert aus fl_modules, Text aus fl_beschreibung
    $value = $role['fl_id'];
    $text = htmlspecialchars($role['fl_beschreibung'], ENT_QUOTES, 'UTF-8');
    $roleSel[$value] =  $text ;
}

$neu['Rolle'] = $roleSel[$neu['fl_id']];

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );
# var_dump($neu);echo "<div class='white'>";
$fr_id = $neu['fr_id'];
echo "<input type='hidden' name='fr_id' value='".$fr_id."' />";
echo "<input type='hidden' name='be_id' value='".$neu['be_id']."' />";
echo "<input type='hidden' name='fl_id' value='".$neu['fl_id']."' />";
# =========================================================================================================
echo $forms->renderHeader('Benutzer '.$benName);
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fr_id',0,'','','readonly');

  # =========================================================================================================
  echo $forms->renderTrenner('Benutzer ');
  # =========================================================================================================
  if ($neu['fr_aktiv'] == '' ){$neu['fr_aktiv'] = 'a';}
  echo $forms->renderRadioFieldFlex('fr_aktiv',['a' => 'Aktiv', 'i' => 'Inaktiv']);
  
  echo $forms->renderTextLikeFieldFlex('Rolle',100,'','','readonly');
  
  echo $forms->renderSelectFieldFlex('descript', $roleSel, '' , '', '','Rollen- Beschreibung') ;
  
  # =========================================================================================================
  echo $forms->renderTrenner('Letzte Änderung');
  # =========================================================================================================
  echo $forms->renderTextLikeFieldFlex('fr_changed_id',0,'','','readonly');
  echo $forms->renderTextLikeFieldFlex('fr_changed_at',0,'','','readonly');
  
# =========================================================================================================

  if (isset($_SESSION['BS_Prim']['BE']) && $_SESSION['BS_Prim']['BE']['roles'] == 'ADM-ALLE') {
  
          echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
          echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";
   }
  
  echo "<p><a href='BenList.php'>Zurück zur Liste</a></p>";
  
  
  echo "</div>";    
# =========================================================================================================
 
if ($debug) {echo "<pre class=debug>Rollendit_ph0_inc.php beendet</pre>";}
?>