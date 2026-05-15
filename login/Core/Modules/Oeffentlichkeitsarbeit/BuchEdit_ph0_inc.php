<?php
/**
 * Liste der Buchbesprechungen, Wartung, Formular
 *
 * @author j. Rohowsky - neu 2019
 *
 */

if ($debug) {
    echo "<pre class=debug>BuchEdit_ph0_inc.php ist gestarted</pre>";
}

use Fharch\Core\Services\FormRendererFlex;

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if ($Zugr == "Alle" || !userBerechtigtOK($Zugr)) {
    $editProtect = true;
    # $readonly = false;
}
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

echo "<input type='hidden' name='bu_id' value='" . $neu['bu_id'] . "' > ";

echo "<input type='hidden' id='recId' name='bu_id' value='".$neu['bu_id']."' >";
echo "<input type='hidden' id='recEigner' value='' >";

# =========================================================================================================
echo $forms->renderHeader('Rezension');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('bu_id');

# =========================================================================================================
echo $forms->renderTrenner('Buch');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('bu_titel', 100);
echo $forms->renderTextLikeFieldFlex('bu_utitel', 100);
echo $forms->renderTextLikeFieldFlex('bu_author', 100);
echo $forms->renderTextLikeFieldFlex('bu_verlag', 100);
echo $forms->renderTextLikeFieldFlex('bu_isbn', 20);
echo $forms->renderTextLikeFieldFlex('bu_preis', 10);
echo $forms->renderTextLikeFieldFlex('bu_seiten', 5);
echo $forms->renderTextLikeFieldFlex('bu_bilder_anz', 5);
echo $forms->renderTextLikeFieldFlex('bu_bilder_art', 50);
echo $forms->renderTextLikeFieldFlex('bu_format', 50);

# =========================================================================================================
echo $forms->renderTrenner('Beschreibung');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('bu_teaser');
echo $forms->renderTextLikeFieldFlex('bu_text');

# =========================================================================================================
echo $forms->renderTrenner('Bewertung');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('bu_bew_ges', 10, 'Bewertung 1.. Eher Grottenschlecht, , 5.. Sehr Gut');
echo $forms->renderTextLikeFieldFlex('bu_bew_bild', 10);
echo $forms->renderTextLikeFieldFlex('bu_bew_txt', 10);

# =========================================================================================================
echo $forms->renderTrenner('Beschreiber');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('bu_editor', 70);
echo $forms->renderTextLikeFieldFlex('bu_ed_id', 10);
echo $forms->renderTextLikeFieldFlex('bu_edit_dat');

if (userBerechtigtOK($Zugr)) {
    # =========================================================================================================
    echo $forms->renderTrenner('Freigabe (für alle Benutzer sichtbar)');
    # =========================================================================================================
    
    echo $forms->renderRadioFieldFlex('bu_frei_stat', array(
        "U" => "U",
        "F" => "F"
    ));
    echo $forms->renderTextLikeFieldFlex('bu_frei_id', 70);
    echo $forms->renderTextLikeFieldFlex('bu_frei_dat');
}
# =========================================================================================================
$checkbox_f = "";
if (userBerechtigtOK($Zugr)) {
    $checkbox_f = "<a href='#' class='toggle-string' data-toggle-group='1'>Foto Daten eingeben/ändern</a>";
}
echo $forms->renderTrenner('Fotos',$checkbox_f);  #
# =========================================================================================================
echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";

$pict_path = "../login/AOrd_Verz/Buch/";

echo "<input type='hidden' name='bu_bild_1' value='" . $neu['bu_bild_1'] . "' >";
echo "<input type='hidden' name='bu_bild_2' value='" . $neu['bu_bild_2'] . "' >";
echo "<input type='hidden' name='bu_bild_3' value='" . $neu['bu_bild_3'] . "' >";
echo "<input type='hidden' name='bu_bild_4' value='" . $neu['bu_bild_4'] . "' >";
echo "<input type='hidden' name='bu_bild_5' value='" . $neu['bu_bild_5'] . "' >";
echo "<input type='hidden' name='bu_bild_6' value='" . $neu['bu_bild_6'] . "' >";

echo "<input type='hidden' id='urhNr' value=''>";
echo "<input type='hidden' id='aOrd' value=''>";

echo "<input type='hidden' id='reSize' value='1754'>";

$Feldlaenge = "100px";
$_SESSION[$module]['Pct_Arr' ] = array();
$num_foto = 6;
$i = 1;
while ($i <= $num_foto) {
    $_SESSION[$module]['Pct_Arr' ][] = array('udir' => $pict_path, 'ko' => 'bu_text_'.$i, 'bi' => 'bu_bild_'.$i, 'rb' => '', 'up_err' => '','f1' => '','f2' => '');
    $i++;
}

UploadForm_M();


if ($Zugr != "Alle" && userBerechtigtOK($Zugr)) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
    # Edit_Send_Button(''); # definiert in Edit_Funcs_v2.php
}

echo "<p><a href='BuchList.php'>Zurück zur Liste</a></p>";

# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>BuchEdit_ph0_inc.php beendet</pre>";
}
?>