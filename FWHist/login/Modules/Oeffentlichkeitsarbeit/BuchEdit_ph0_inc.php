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

use FWHist\Auth\AccessGuard;
use FWHist\Services\FormRendererFlex_Ext;

$editProtect = !$canEdit;
$readonly = "";

$forms = new FormRendererFlex_Ext($meta, $phase,  $neu, [], $editProtect, $module );

$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige

if ((int)$neu['bu_id'] !== 0) {
    $readonly = 'readonly';
    $newRec = true;
}

echo "<input type='hidden' name='bu_id' value='" . $neu['bu_id'] . "' > ";
// CSRF-Token (wird in BuchEdit.php beim Speichern geprüft)
echo "<input type='hidden' name='csrf' value='" . htmlspecialchars(AccessGuard::csrfToken(), ENT_QUOTES, 'UTF-8') . "' >";

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

if ($canRelease) {
    # =========================================================================================================
    echo $forms->renderTrenner('Freigabe (für alle Benutzer sichtbar)');
    # =========================================================================================================

    echo $forms->renderSelectFieldFlex('bu_frei_stat', array(
        "U" => "nicht Freigegeben",
        "F" => "Freigegeben == Sichtbar"
    ));
    // Freigeber (bu_frei_id) und Datum (bu_frei_dat) setzt der Server beim Speichern.
}
# =========================================================================================================
echo $forms->renderTrenner('Fotos');  #
# =========================================================================================================
echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";

$pict_path =  $basePath . "login/AOrd_Verz/Buch/";

echo "<input type='hidden' name='bu_bild_1' value='" . htmlspecialchars((string) $neu['bu_bild_1'], ENT_QUOTES, 'UTF-8') . "' >";
echo "<input type='hidden' name='bu_bild_2' value='" . htmlspecialchars((string) $neu['bu_bild_2'], ENT_QUOTES, 'UTF-8') . "' >";
echo "<input type='hidden' name='bu_bild_3' value='" . htmlspecialchars((string) $neu['bu_bild_3'], ENT_QUOTES, 'UTF-8') . "' >";
echo "<input type='hidden' name='bu_bild_4' value='" . htmlspecialchars((string) $neu['bu_bild_4'], ENT_QUOTES, 'UTF-8') . "' >";
echo "<input type='hidden' name='bu_bild_5' value='" . htmlspecialchars((string) $neu['bu_bild_5'], ENT_QUOTES, 'UTF-8') . "' >";
echo "<input type='hidden' name='bu_bild_6' value='" . htmlspecialchars((string) $neu['bu_bild_6'], ENT_QUOTES, 'UTF-8') . "' >";

echo "<input type='hidden' id='eigner' value='0'>";
echo "<input type='hidden' id='urhNr' value='0'>";
echo "<input type='hidden' id='urhNr' value='0'>";

$pictPath = $basePath . "/FWHist/login/AOrd_Verz/Buch";

$pic_cnt = 6;

$i = 1;
while ($i <= $pic_cnt) {
    $pct_arr[] = array('udir' => $pictPath, 'ko' => '', 'bi' => 'bu_bild_'.$i , 'rb' => '', 'up_err' => '','f1'=>'','f2'=>'');
    $i++;
}

if ($canEdit) {
    Upload_Form($pic_cnt, $pct_arr, $newRec);
} else {
    // Nur-Lese-Ansicht (ONU-ALL, ONU-GAST, extern): vorhandene Bilder anzeigen, keine Upload-Steuerung
    for ($i = 1; $i <= $pic_cnt; $i++) {
        $file = basename((string) ($neu['bu_bild_' . $i] ?? ''));
        if ($file === '') {
            continue;
        }
        $fileH = htmlspecialchars($file, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $url = htmlspecialchars($pictPath . '/' . rawurlencode($file), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['gif', 'ico', 'jpeg', 'jpg', 'png', 'tiff'], true)) {
            echo "<p><a href='{$url}' target='_blank' rel='noopener noreferrer'><img src='{$url}' alt='{$fileH}' height='200'></a></p>";
        } else {
            echo "<p><a href='{$url}' target='_blank' rel='noopener noreferrer'>{$fileH}</a></p>";
        }
    }
}


if ($canEdit) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
    # Edit_Send_Button(''); # definiert in Edit_Funcs_v2.php
}

echo "<p><a href='BuchList.php'>Zurück zur Liste</a></p>";

# =========================================================================================================

#echo "<script src='" . $basePath . "/FWHist/login/Services/js/toggleLibs.js' ></script>";
if ($canEdit) {
    #echo "<script src='" . $basePath . "/FWHist/login/Services/js/FotoUplLibs.js' ></script>";
    # echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js' ></script>";
    echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Urheber.js' ></script>";
}

if ($debug) {
    echo "<pre class=debug>BuchEdit_ph0_inc.php beendet</pre>";
}
?>