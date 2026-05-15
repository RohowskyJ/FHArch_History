<?php

/**
 * Home-Page setup. Mode, Kenndaten, Funktionen
 *
 * @author Josef Rohowsky - neu 2023
 *
 *
 */

/**
 * Includes-Liste
 * enthält alle jeweils includierten Scritpt Files
 */
$_SESSION[$module]['Inc_Arr'][] = "Proj_Conf_Edit_ph0.inc.php";
var_dump($neu);
if ($debug) {
    echo "<pre class=debug>Proj_Config_Edit_ph0.inc.php ist gestarted</pre>";
}
console_log('proj start ph0');
echo "<div class='white'>";

# =========================================================================================================
Edit_Tabellen_Header('Definitionen');
# =========================================================================================================

# =========================================================================================================
Edit_Separator_Zeile('Betreiber- Daten');
# =========================================================================================================
# var_dump($Tabellen_Spalten_typ);
Edit_Daten_Feld('c_Institution', 65);

Edit_Daten_Feld('c_Vereinsreg', 15);

Edit_Daten_Feld('c_Eignr', 5);
Edit_Daten_Feld('c_Verantwortl', 65);
Edit_Daten_Feld('c_Ver_Tel', 30);
Edit_Daten_Feld('c_email', 60);
Edit_Daten_Feld('c_Homepage', 60);
Edit_Daten_Feld('c_ptyp', 60);
Edit_Daten_Feld('c_store', 60);
Edit_Daten_Feld('c_def_pw', 60);
Edit_Daten_Feld('c_Perr', 60);
Edit_Daten_Feld('c_Debug', 60);

# =========================================================================================================
Edit_Separator_Zeile('Beschreibungs- Nutzungs- Daten');
# =========================================================================================================

Edit_Select_Feld('c_mode', array(
    "Single" => "Keine Mandanten",
    "Mandanten" => "Mandanten"
));

Edit_Select_Feld('c_Wartung', array(
    "J" => "System in Wartung",
    "N" => "System in Betrieb",
    "U" => "System in Sonderzustand - siehe Wartungsgrund"
));

Edit_Daten_Feld('c_Wart_Grund', 65);

# =========================================================================================================
$checkbox_f = "<label> &nbsp; &nbsp; <input type='checkbox' id='toggleGroup1' checked > Foto Daten eingeben/ändern </label>"; # $checked = 'checked';
    
Edit_Separator_Zeile('Fotos',$checkbox_f);  #
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

VF_Upload_Form_M(); // _M im Original

const Ja_Nein = array(
    'J' => 'Ja',
    'N' => 'Nein'
);
Edit_Select_Feld('c_Module_1', Ja_Nein);
Edit_Select_Feld('c_Module_2', Ja_Nein);
Edit_Select_Feld('c_Module_3', Ja_Nein);
Edit_Select_Feld('c_Module_4', Ja_Nein);
Edit_Select_Feld('c_Module_5', Ja_Nein);
Edit_Select_Feld('c_Module_6', Ja_Nein);
Edit_Select_Feld('c_Module_7', Ja_Nein);
Edit_Select_Feld('c_Module_8', Ja_Nein);
Edit_Select_Feld('c_Module_9', Ja_Nein);
Edit_Select_Feld('c_Module_10', Ja_Nein);
Edit_Select_Feld('c_Module_11', Ja_Nein);
Edit_Select_Feld('c_Module_12', Ja_Nein);
Edit_Select_Feld('c_Module_13', Ja_Nein);
Edit_Select_Feld('c_Module_14', Ja_Nein);
Edit_Select_Feld('c_Module_15', Ja_Nein);
# =========================================================================================================
Edit_Tabellen_Trailer();

echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";

echo "</div>";
# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>Proj_Config_Edit_ph0.inc.php beendet</pre>";
}
