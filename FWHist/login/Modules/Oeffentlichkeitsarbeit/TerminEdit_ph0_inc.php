<?php

/**
 * Liste der Veranstaltungstermine, Wartung, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 */

if ($debug) {
    echo "<pre class=debug>TerminEdit_ph0.inc.php ist gestarted</pre>";
}

use FWHist\Services\FormRendererFlex_Ext;

$editProtect = !$canEdit;
$readonly = "";

$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige

if ((int)$neu['va_id'] !== 0) {
    $readonly = 'readonly';
    $newRec = $canEdit;
}

$forms = new FormRendererFlex_Ext($meta, $phase,  $neu, [], $editProtect, $module );
if ($canEdit) {
    echo \FWHist\Auth\AccessGuard::csrfField();
}

echo "<input type='hidden' id='recId' name='va_id' value='".$neu['va_id']."' >";
echo "<input type='hidden' id='recEigner' value='' >";

$cdate = date("Y-m-d");
#echo __FILE__ . " " . __LINE__ . " cdate $cdate <br>";
#echo $Err_Msg;

$Edit_Funcs_FeldName = false; // Feldname der Tabelle wird nicht angezeigt !!
                              # =========================================================================================================
echo $forms->renderHeader('Veranstaltungs- Daten');
# =========================================================================================================

if ( $neu['va_id'] !== 0 & $neu['va_datum']<$cdate & $neu['va_datum']<>'0000-00-00' )
{
    echo "<h2>Daten können nicht mehr geändert (nicht in die Tabelle gesichert) werden. </h2>";
    $editProtect = true;
}

if ($neu['va_id'] == "0") {
    echo $forms->renderTextLikeFieldFlex('va_id', 0, 'Neue Veranstaltung');
} else {
    echo $forms->renderTextLikeFieldFlex('va_id', 12);
}

if (! empty($neu['va_angelegt'])) { # =========================================================================================================//
   echo $forms->renderTrenner("Veranstaltungs- Status");
    # =========================================================================================================
    echo $forms->renderTextLikeFieldFlex('va_angelegt', 0, ' von ' . $neu['va_ang_uid']);
    if (! is_null($neu['va_aenderung'])) {
        echo $forms->renderTextLikeFieldFlex('va_aenderung', 0, ' von ' . $neu['va_aend_uid']);
    }
}

# =========================================================================================================
echo $forms->renderTrenner('Datum und Zeit');
# =========================================================================================================
$min_date = date('Y-m-d');
echo $forms->renderDatePickerFieldFlex('va_datum','', "type='date' required min='$cdate'");
echo $forms->renderTextLikeFieldFlex('va_begzt', 5, 'Format: hh:mm', "type='time'");

echo $forms->renderDatePickerFieldFlex('va_end_dat', '',  " min='$cdate'");

echo $forms->renderTextLikeFieldFlex('va_endzt', 5, 'Format: hh:mm', "type='time'");

# =========================================================================================================
echo $forms->renderTrenner('Titel und Beschreibung');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('va_titel', 100, '', 'required');
echo $forms->renderTextLikeFieldFlex('va_beschr');
echo $forms->renderSelectFieldFlex('va_umfang', VF_Term_Umfang);
echo $forms->renderSelectFieldFlex('va_kateg', VF_Term_Kateg);

echo $forms->renderSelectFieldFlex('va_anm_erf', VF_JN);

# =========================================================================================================
echo $forms->renderTrenner('Veranstaltungs- Ort');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('va_inst', 50);
echo $forms->renderTextLikeFieldFlex('va_adresse', 50);
echo $forms->renderTextLikeFieldFlex('va_plz', 10);
echo $forms->renderTextLikeFieldFlex('va_ort', 50);

echo $forms->renderTextLikeFieldFlex('va_staat',50);
if (!$editProtect  ){ // || $cdate > $neu['va_datum']
    echo $forms->AutoCompStaat();
}

echo $forms->renderTextLikeFieldFlex('va_bdld',50);
if (!$editProtect  ){ // if ($cdate > $neu['va_datum'] ){
    echo $forms->AutoCompBdld();
}

# =========================================================================================================
echo $forms->renderTrenner('Fotos');  #
# =========================================================================================================

#echo $forms->renderTextLikeFieldFlex('va_bild', 50);
echo "<input type='hidden' name='va_bild_1' value='" . $neu['va_bild_1'] . "'>";
echo "<input type='hidden' name='va_bild_2' value='" . $neu['va_bild_2'] . "'>";
echo "<input type='hidden' name='va_bild_3' value='" . $neu['va_bild_3'] . "'>";
echo "<input type='hidden' name='va_bild_4' value='" . $neu['va_bild_4'] . "'>";


$cjahr = substr($neu['va_datum'],0,4); #date('Y');

echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";

$pict_path = $systemBasePath . "login/AOrd_Verz/Termine/" . $cjahr . "/";

echo "<input type='hidden' id='urhNr' value=''>";
echo "<input type='hidden' id='aOrd' value=''>";

echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";

/** Variablen zur Fehlervermeidung */
$neu['ei_id'] = 9;
$neu['ad_sg'] = $neu['ad_sub_sg'] = "";

$pictPath = $basePath . "/FWHist/login/AOrd_Verz/Termine/" . $cjahr . "/";

$Feldlaenge = "100px";

$pic_cnt = 4;

$i = 1;
while ($i <= $pic_cnt) {
    $pct_arr[] = array('udir' => $pictPath, 'ko' => '', 'bi' => 'va_bild_'.$i , 'rb' => '', 'up_err' => '','f1'=>'','f2'=>'');
    $i++;
}

Upload_Form($pic_cnt, $pct_arr, $newRec);


#===================================================

echo $forms->renderTextLikeFieldFlex('va_internet', 50);
echo $forms->renderTextLikeFieldFlex('va_anm_text', 50);
echo $forms->renderTextLikeFieldFlex('va_anmeld_end', 10, '', "type='date'   min='$cdate'");

if ($canEdit) {
    # =========================================================================================================
    #echo $forms->renderTrenner('Platzplanung');
    # =========================================================================================================
    echo $forms->renderTextLikeFieldFlex('va_raum', 50, ' in Lokation');
    echo $forms->renderTextLikeFieldFlex('va_plaetze', 5, '', 'type="number" min=0 max =9999 required');
    echo $forms->renderTextLikeFieldFlex('va_warte', 5, '', 'type="number" min=0 max=9999 required');

    if ($neu['va_id'] !== "0") { # =========================================================================================================
       echo $forms->renderTrenner('Aktuelle Platzbelegung');
        # =========================================================================================================
        echo $forms->renderTextLikeFieldFlex('va_akt_pl');
        echo $forms->renderTextLikeFieldFlex('va_wl_pl');
        echo $forms->renderTextLikeFieldFlex('va_anz_anmeld');
    }

    # =========================================================================================================
   echo $forms->renderTrenner('Kostenbeteiligungen, Verantwortlicher');
    # =========================================================================================================

    echo $forms->renderTextLikeFieldFlex('va_beitrag_m', 10);

    echo $forms->renderTextLikeFieldFlex('va_beitrag_g', 10);
    echo $forms->renderTextLikeFieldFlex('va_kontakt', 50);
    echo $forms->renderTextLikeFieldFlex('va_admin_email', 50);
    echo $forms->renderTextLikeFieldFlex('va_link_einladung', 100);

    $Edit_Funcs_Protect = False;
    if (! empty($neu['va_angelegt'])) {}
}

if ($cdate > $neu['va_datum'] && $neu['va_id'] !== 0) {} else {
    if ($canEdit) {
        echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
        echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";
    }
}

echo "<p><a href='TerminList.php'>Zurück zur Liste</a></p>";

# =========================================================================================================

echo "<script src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Staat.js' ></script>";
echo "<script src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Bdld.js' ></script>";
echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Urheber.js' ></script>";

if ($debug) {
    echo "<pre class=debug>TerminEdit_ph0.php beendet</pre>";
}
?>