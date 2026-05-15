
<?php
/**
 * Lister der Anbote / Nachfragen, Wartung, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 */

if ($debug) {
    echo "<pre class=debug>MarktplEdit_ph0.inc.php ist gestarted</pre>";
}

use Fharch\Core\Services\FormRendererFlex;

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if ($Zugr == "Alle" || !userBerechtigtOK($Zugr)) {
    $editProtect = true;
    # $readonly = false;
}
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

echo "<input type='hidden' id='recId' name='bs_id' value='".$neu['bs_id']."' >";
echo "<input type='hidden' id='recEigner' value='' >";

$today = date("Y-m_d");

echo $forms->renderHeader('Angebot / Nachfrage');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('bs_id');
echo "<input type='hidden'name='bs_id' value='" . $neu['bs_id'] . "' >";
# =========================================================================================================
echo $forms->renderTrenner('Aussendung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('bs_startdatum', 10, '', "type='date' min='$today'");
echo $forms->renderTextLikeFieldFlex('bs_enddatum', 10, '', "type='date' min='$today'");

echo "<input type='hidden'name='bs_typ' value='" . $neu['bs_typ'] . "' >";

echo $forms->renderRadioFieldFlex('bs_typ', array(
    "B" => "Biete",
    "S" => "Suche"
));
echo $forms->renderTextLikeFieldFlex('bs_kurztext');
echo $forms->renderTextLikeFieldFlex('bs_text');

echo $forms->renderTextLikeFieldFlex('bs_email_1', 50);

echo $forms->renderTextLikeFieldFlex('bs_email_2', 50);

# =========================================================================================================
$checkbox_f = "";
if (userBerechtigtOK($Zugr)) {
    $checkbox_f = "<a href='#' class='toggle-string' data-toggle-group='1'>Foto Daten eingeben/ändern</a>";
}
echo $forms->renderTrenner('Fotos',$checkbox_f);  #
# =========================================================================================================
echo "<input type='hidden' name='bs_bild_1' value='" . $neu['bs_bild_1'] . "'>";
echo "<input type='hidden' name='bs_bild_2' value='" . $neu['bs_bild_2'] . "'>";
echo "<input type='hidden' name='bs_bild_3' value='" . $neu['bs_bild_3'] . "'>";
echo "<input type='hidden' name='bs_bild_4' value='" . $neu['bs_bild_4'] . "'>";

$pict_path = $path2ROOT."login/AOrd_Verz/Biete_Suche/";

echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";


echo "<input type='hidden' id='urhNr' value=''>";
echo "<input type='hidden' id='aOrd' value=''>";

echo "<input type='hidden' id='reSize' value='1754'>";

$Feldlaenge = "100px";

$_SESSION[$module]['Pct_Arr' ] = array();
$num_foto = 4;
$i = 1;
while ($i <= $num_foto) {
    $_SESSION[$module]['Pct_Arr' ][] = array('udir' => $pict_path, 'ko' => '', 'bi' => 'bs_bild_'.$i, 'rb' => '', 'up_err' => '','f1' => '','f2' => '');
    
    echo "<input type='hidden' id='aOrd_$i' value='Biete_Suche/'>";
    $i++;
}

UploadForm_M();

# =========================================================================================================
echo $forms->renderTrenner('Beschreiber');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('bs_changed_id');
echo $forms->renderTextLikeFieldFlex('bs_changed_at');

if ($Zugr != "Alle" && userBerechtigtOK($Zugr)) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>"; 
}

echo "<p><a href=MarktplList.php?'>Zurück zur Liste</a></p>";

if ($debug) {
    echo "<pre class=debug>MarktplEdit_ph0.php beendet</pre>";
}
?>