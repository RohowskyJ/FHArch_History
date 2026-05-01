<?php

/**
 * Home-Page setup. Mode, Kenndaten, Funktionen
 *
 * @author Josef Rohowsky - neu 2023
 *
 *
 */

# var_dump($neu);
if ($debug) {
    echo "<pre class=debug>ConfEdit_ph0_inc.php ist gestarted</pre>";
}

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if (!userBerechtigtOK($Zugr) ) {
    # $editProtect = true;
    # $readonly = false;
}

use Fharch\Core\Services\FormRendererFlex;
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

console_log('proj start ph0');
echo "<div class='white'>";

# =========================================================================================================
echo $forms->renderHeader("HP- Seiten Definitionen"); 
# =========================================================================================================

# =========================================================================================================
echo $forms->renderTrenner('Betreiber- Daten');
# =========================================================================================================
# var_dump($Tabellen_Spalten_typ);
  echo $forms->renderTextLikeFieldFlex('c_Institution', 65);

  echo $forms->renderTextLikeFieldFlex('c_Vereinsreg', 15);

  echo $forms->renderTextLikeFieldFlex('c_Eignr', 5);
  echo $forms->renderTextLikeFieldFlex('c_Verantwortl', 60);
  echo $forms->renderTextLikeFieldFlex('c_Ver_Tel', 30);
  echo $forms->renderTextLikeFieldFlex('c_email', 60);
  echo $forms->renderTextLikeFieldFlex('c_Homepage', 60);
  echo $forms->renderTextLikeFieldFlex('c_ptyp', 60);
  echo $forms->renderTextLikeFieldFlex('c_store', 60);
  echo $forms->renderTextLikeFieldFlex('c_def_pw', 60);
  echo $forms->renderTextLikeFieldFlex('c_Perr', 60);
  echo $forms->renderTextLikeFieldFlex('c_Debug', 60);
  echo $forms->renderTextLikeFieldFlex('c_bpath', 60);
  echo $forms->renderTextLikeFieldFlex('c_miBeitr', 60);

# =========================================================================================================
echo $forms->renderTrenner('Beschreibungs- Nutzungs- Daten');
# =========================================================================================================

echo $forms->renderSelectFieldFlex('c_mode', array(
    "Single" => "Keine Mandanten",
    "Mandanten" => "Mandanten"
));

echo $forms->renderSelectFieldFlex('c_Wartung', array(
    "J" => "System in Wartung",
    "N" => "System in Betrieb",
    "U" => "System in Sonderzustand - siehe Wartungsgrund"
));

  echo $forms->renderTextLikeFieldFlex('c_Wart_Grund', 65);

# =========================================================================================================
$checkbox_f = "<label> &nbsp; &nbsp; <input type='checkbox' id='toggleGroup1' checked > Foto Daten eingeben/ändern </label>"; # $checked = 'checked';
    
echo $forms->renderTrenner('Fotos',$checkbox_f);  #
# =========================================================================================================

echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";
echo "<input type='hidden' name='c_bild_1' value='".$neu['c_bild_1']."' class='monitor' >";
echo "<input type='hidden' name='c_bild_2' value='".$neu['c_bild_2']."' >";

echo "<input type='hidden' id='sammlung' value=''>";
echo "<input type='hidden' id='eigner' value=''>";

echo "<input type='hidden' id='aOrd' value=''>";
echo "<input type='hidden' id='urhNr' value=''>";

$pict_path = 'imgs/';

$_SESSION[$module]['Pct_Arr' ] = array();
$num_foto = 2;
$i = 1;
while ($i <= $num_foto) {
    $_SESSION[$module]['Pct_Arr' ][] = array('udir' => $pict_path, 'ko' => '', 'bi' => 'c_bild_'.$i, 'rb' => '', 'up_err' => '','f1' => '','f2' => '');
    $i++;
}

UploadForm_M(); // _M im Original

const Ja_Nein = array(
    'J' => 'Ja',
    'N' => 'Nein'
);

if (userBerechtigtOK($Zugr) ) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";
}


echo "</div>";
# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>S_ConfEdit_ph0.inc.php beendet</pre>";
}
