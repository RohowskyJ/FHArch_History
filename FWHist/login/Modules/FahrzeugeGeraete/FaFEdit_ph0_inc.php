<?php

/**
 * Formular für Motorisierte Fahrzeuge
 * 
 * @author Josef Rohowsky - neu 2026
 */

use FWHist\Services\FormRendererFlex_Ext;

if ($debug) {
    echo "<pre class=debug>FaFEdit_ph0_inc ist gestarted</pre>";
}
# var_dump($neu);

$editProtect = !$ber;
$readonly = "";

$forms = new FormRendererFlex_Ext($meta, $phase,  $neu, [], $editProtect, $module );

/** Werte für Foto Mgmt */
$dataSetAct = "";
if ($neu['fz_id'] == 0) { // Neueingabe
    $hide_area = 0;
} else {
    $hide_area = 1;
    $dataSetAct = "data-active-index='0'";
}

$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige

if ((int)$neu['fz_id'] !== 0) {
    $readonly = 'readonly';
    $newRec = true;
}
$blindOpen = $newRec ? '' : ' open';

echo "<input type='hidden' id='recId' name='fz_id' value='".$neu['fz_id']."'/>";
echo "<input type='hidden' id='recEigner'  value='" . $neu['ei_id'] . "'/>";
echo "<input type='hidden' id='ei_id' name='ei_id' value='" . $neu['ei_id'] . "'/>";
if (!$editProtect) {
    echo \FWHist\Auth\AccessGuard::csrfField();
}
echo "<input type='hidden' id='hide_area' value='$hide_area'>";

# =========================================================================================================
echo $forms->renderHeader('Motorisierte Fahrzeuge von Eigentümer '); //.$_SESSION['Eigner']['eig_name']);
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fz_id');
echo $forms->renderTextLikeFieldFlex('ei_id');

echo $forms->renderTextLikeFieldFlex('fz_sammlg', 20);

echo $forms->renderMultiSelectFieldFlex(
    fieldName: 'fz_sammlg',
    configId: 'SAMMLUNG',
    apiUrl: '../../../login/Services/API/MultiSelectAPI.php'
    );

# =========================================================================================================
echo $forms->renderTrenner('Fahrzeug/Geräte- Beschreibung');
# =========================================================================================================


echo $forms->renderTextLikeFieldFlex('fz_taktbez', 50);
if (!$editProtect) {
    AutoCompForm_Abk('MA_F'); //  $neu['fz_sammlg']
    echo "Zum Ändern / Suchen - hier anklicken";
}
echo "</div>";
echo "</li>";
echo "</ul>";
// ende accordion für Takt. Bez

echo $forms->renderTextLikeFieldFlex('fz_name', 50);

echo $forms->renderTextLikeFieldFlex('fz_hist_bezeichng', 50);

$opt_aera = VF_FZG_Aera;
echo $forms->renderSelectFieldFlex('fz_zeitraum', $opt_aera, '');

echo $forms->renderTextLikeFieldFlex('fz_allg_beschr');

echo $forms->renderTextLikeFieldFlex('fz_beschreibg_det');

echo $forms->renderTextLikeFieldFlex('fz_indienstst', 10, 'Datum YYYY-MM-DD oder zumindest Jahr der Indienst- Stellung');
echo $forms->renderTextLikeFieldFlex('fz_ausdienst', 10, 'Datum YYYY-MM-DD oder zumindest Jahr der Ausserdienst- Stellung');

echo $forms->renderSelectFieldFlex('fz_zustand', VF_Zustand, '');

echo $forms->renderTextLikeFieldFlex('fz_herstell_fg', 100);
// accordion für Fahrgestell- Hersteller
echo "<ul id='fg-accordion' class='accordionjs' $dataSetAct >"; // fg- für Fahrzeughersteller
echo "<li>";
echo "<div>";
$readOnly = 'readonly'; #$Edit_Funcs_Protect = true;
# Edit_Daten_Feld_Button('fz_herstell_fg', 70, '', '');
if (!$editProtect) {
    echo $forms->AutoCompfirma('F');
}
echo "</div>";
echo "</li>";
echo "</ul>";
// ende accordion für  Fahrgestell- Hersteller

echo $forms->renderTextLikeFieldFlex('fz_typ', 70);
echo $forms->renderTextLikeFieldFlex('fz_modell', 50);

echo $forms->renderTextLikeFieldFlex('fz_motor', 50);
echo $forms->renderTextLikeFieldFlex('fz_antrieb', 50);

echo $forms->renderTextLikeFieldFlex('fz_geschwindigkeit', 10, ' km/Std');

echo "<input type='hidden' name='fz_aufbauer' value='".$neu['fz_aufbauer']."' >";

echo $forms->renderTextLikeFieldFlex('fz_aufbauer', 100);
// accordion für Aufbauer
echo "<ul id='au-accordion' class='accordionjs' $dataSetAct >"; // au- für Aufbauer
echo "<li>";
echo "<div>";
// $readOnly = 'readonly'; #$Edit_Funcs_Protect = True;
# Edit_Daten_Feld_Button('fz_aufbauer', 50,'','');
if (!$editProtect) {
    /*
    echo "Zum Ändern / Suchen - hier anklicken";
    echo "</div>";
    echo "<div>";
    AutoCompForm_Aufbau('A');
    */
    echo $forms->AutoCompAufbau('A');
}
echo "</div>";
echo "</li>";
echo "</ul>";
// ende accordion für Aufbauer

echo $forms->renderTextLikeFieldFlex('fz_aufb_typ', 50);
echo $forms->renderTextLikeFieldFlex('fz_besatzung', 10);

echo $forms->renderTextLikeFieldFlex('fz_baujahr', 4);

# =========================================================================================================</a>";
echo $forms->renderTrenner('Fotos');  #
# =========================================================================================================
/*
 echo "<div>";
 */
echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";
echo "<input type='hidden' name='fz_bild_1' value='".$neu['fz_bild_1']."' class='monitor' >";
echo "<input type='hidden' name='fz_bild_2' value='".$neu['fz_bild_2']."' >";
echo "<input type='hidden' name='fz_bild_3' value='".$neu['fz_bild_3']."' >";
echo "<input type='hidden' name='fz_bild_4' value='".$neu['fz_bild_4']."' >";

echo "<input type='hidden' id='fz_sammlg' value='".$neu['fz_sammlg'] ."'>";
echo "<input type='hidden' id='eigner' value='".$neu['ei_id'] ."'>";
echo "<input type='hidden' id='urhNr' value=''>";

$pictPath = $basePath . "/FWHist/login/AOrd_Verz/";
$pic_cnt = 4;

$i = 1;
while ($i <= $pic_cnt) {
    $pct_arr[] = array('udir' => $pictPath, 'ko' => 'fz_b_' . $i . '_komm', 'bi' => 'fz_bild_'.$i , 'rb' => '', 'up_err' => '','f1'=>'','f2'=>'');
    $i++;
}

Upload_Form($pic_cnt, $pct_arr, $newRec);
// ende Foto

// Block für eingebaute Ausrüstung
echo "<details class='block-blind'$blindOpen>";
echo "<summary>Eingebaute Ausrüstung - für Details anklicken</summary>";
echo "<div class='block-blind-content'>";
echo $forms->renderTextLikeFieldFlex('fz_l_tank', 70);
echo $forms->renderTextLikeFieldFlex('fz_l_monitor', 70);
echo $forms->renderTextLikeFieldFlex('fz_l_pumpe', 70);
echo $forms->renderTextLikeFieldFlex('fz_t_kran', 70);
echo $forms->renderTextLikeFieldFlex('fz_t_winde', 70);
echo $forms->renderTextLikeFieldFlex('fz_t_leiter', 70);
echo $forms->renderTextLikeFieldFlex('fz_t_abschlepp', 70);
echo $forms->renderTextLikeFieldFlex('fz_t_beleuchtg', 70);
echo $forms->renderTextLikeFieldFlex('fz_t_geraet', 70);
echo $forms->renderTextLikeFieldFlex('fz_t_strom', 70);
echo $forms->renderTextLikeFieldFlex('fz_g_atemsch', 70);
echo "</div>";
echo "</details>";
// ende Block für eingebaute Ausrüstung

// Block für CTIF zertifikat
echo "<details class='block-blind'$blindOpen>";
echo "<summary>CTIF Zertifizierung - für Details anklicken</summary>";
echo "<div class='block-blind-content'>";
echo $forms->renderSelectFieldFlex('fz_ctif_klass', VF_CTIF_Class);
echo $forms->renderTextLikeFieldFlex('fz_ctif_date', 10);
echo $forms->renderTextLikeFieldFlex('fz_ctif_darst_jahr', 4);
echo $forms->renderTextLikeFieldFlex('fz_ctif_juroren', 70);
echo "</div>";
echo "</details>";
// ende Block für CTIF zertifikat

# =========================================================================================================
echo $forms->renderTrenner('Datenfreigabe');
# =========================================================================================================

echo $forms->renderSelectFieldFlex('fz_eigent_freig', VF_JN, '');
echo $forms->renderSelectFieldFlex('fz_verfueg_freig', VF_JN, '');

/*
 echo $forms->renderTextLikeFieldFlex('fz_pruefg_id', 1);
 echo $forms->renderTextLikeFieldFlex('fz_pruefg',1);
 */
echo "<input type='hidden' name='fz_pruefg_id' value='".$neu['fz_pruefg_id']."' >";
echo "<input type='hidden' name='fz_pruefg' value='".$neu['fz_pruefg']."'  >";
# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fz_changed_id',10);
echo $forms->renderTextLikeFieldFlex('fz_changed_at', 20);

# =========================================================================================================

?>
<!-- Bereich zur Anzeige der aktuellen Werte -->
<div id="anzeige"></div>

<!-- Bereich für Debug-Infos  -->
<div id="debug">Wartet auf Eingaben...
</div>
<?php

if (!$editProtect) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='FzAList.php?ei_id=" . $neu['ei_id'] . "'>Zurück zur Liste</a></p>";

$proj = "Z";
/**
 * Anzeige der bisherigen Besitzer
 */
require "FaEList_inc.php";
/** Anzeige der Dokumente zum Fahrzeug */
require "FaAList_inc.php";
echo "</div>"; # ende dropdown Eigentümer


/** notwendige css füe jalousie einfügen */
echo "<style>
    .block-blind {
        margin: .6rem 0;
        border: 1px solid #d2d2d2;
    }
    .block-blind > summary {
        padding: .65rem .8rem;
        background: #f2f2f2;
        cursor: pointer;
        font-weight: 600;
    }
    .block-blind[open] > summary {
        background: #f9d9bd;
    }
    .block-blind > summary:focus-visible {
        outline: 2px solid #555;
        outline-offset: 2px;
    }
    .block-blind-content {
        padding: .5rem;
    }
</style>";


echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Urheber.js' defer></script>"; 
echo "<script src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Firma.js' defer></script>";
echo "<script src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Abk.js' defer></script>";
echo "<script src='" . $basePath . "/FWHist/login/Services/js/fw_multi_select.js' defer></script>";
?>
<script src="<?= $basePath?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath?>/FWHist/js/list-tables.js"></script>
<?php 
# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>FaFEdit_ph0.inc.php beendet</pre>";
}

# echo $page->renderFooter();