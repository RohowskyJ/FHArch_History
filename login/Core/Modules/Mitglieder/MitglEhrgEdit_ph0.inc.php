<?php

use Fharch\Core\Services\FormRendererFlex;

/**
 * Liste der vom Verein verliehenen Ehrungen, Wartung, Formular
 *
 * @author Josef Rohowsky - neu 2023
 *
 *
 */

#var_dump($neu);
if ($debug) {
    echo "<pre class=debug>MitglEhrgEdit_ph0.inc.php ist gestarted</pre>";
}

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if (!userBerechtigtOK($Zugr) ) {
    $editProtect = true;
    # $readonly = false;
}
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

echo "<div class='white'>";

echo "<input name='me_id' type='hidden' value='" . $neu['me_id'] . "' > ";
echo "<input name='mi_id' type='hidden' value='" . $neu['mi_id'] . "' > ";

# =========================================================================================================
echo $forms->renderHeader('Ehrungen');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('me_id',0,'','','readonly'); // ,'', '', '','','readonly'
echo $forms->renderTextLikeFieldFlex('mi_id',0,'','','readonly');

# =========================================================================================================
echo $forms->renderTrenner('Daten der Auszeichnung / Ehrung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('me_ehrung',100);
echo $forms->renderTextLikeFieldFlex('me_eh_datum',12);
echo $forms->renderTextLikeFieldFlex('me_begruendg', 256 );

# =========================================================================================================
echo $forms->renderTrenner('Fotos');
# =========================================================================================================
echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";
echo "<input type='hidden' name='me_bild1' value='" . $neu['me_bild1'] . "'>";
echo "<input type='hidden' name='me_bild2' value='" . $neu['me_bild2'] . "'>";
echo "<input type='hidden' name='me_bild3' value='" . $neu['me_bild3'] . "'>";
echo "<input type='hidden' name='me_bild4' value='" . $neu['me_bild4'] . "'>";

$pict_path = "AOrd_Verz/1/MITGL/";

$Feldlaenge = "150px";

$pic_arr = array(
    "01" => "|||me_bild1",
    "02" => "|||me_bild2",
    "03" => "|||me_bild3",
    "04" => "|||me_bild4"
);
Multi_Foto($pic_arr);

# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('me_changed_id');
echo $forms->renderTextLikeFieldFlex('me_changed_at');

# =========================================================================================================

if (userBerechtigtOK($Zugr)) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

if (isset($_SESSION[$module]['Return']) AND $_SESSION[$module]['Return']) {
    echo "<p><a href='MitglEhrgList.php' >Zurück zur Liste</a></p>";
} else {
    echo "<p><a href='MitglEhrgEdit.php' >Zurück zur Liste</a></p>";
}

echo "</div>";

if ($debug) {
    echo "<pre class=debug>MitglEhrgEdit_ph0.inc.php beendet </pre>";
}

?>