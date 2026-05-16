<?php

/**
 * Museums- Daten- Wartung, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 * 
 */
use Fharch\Core\Services\FormRendererFlex;
use Fharch\Core\Services\AutoCompleteAPI;


if ($debug) {
    echo "<pre class=debug>MuseenEdit_ph0_inc.php ist gestarted</pre>";
}


$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

/** Vars für accordion */
$accUpd = 1;

/**Zugriffsteuerung */
if ($Zugr == "Alle" || !userBerechtigtOK($Zugr)) {
    $editProtect = true;
    # $readonly = false;
    $accUpd = 0; // für accordion
}

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

/** Werte für Foto Mgmt */
$dataSetAct = "";
if ($neu['mu_id'] == 0) { // Neueingabe
    $hide_area = 0;
} else {
    $hide_area = 1;
    $dataSetAct = "data-active-index='0'";
}

/** input für Accordion */
echo "<input type='hidden' id='recId' value='" . $neu['mu_id'] . " >";
echo "<input type='hidden' id='accUpd' value='$accUpd' >";
echo "<input type='hidden' id='recEigner' value='" . $neu['mu_eigner'] . " >";

echo "<div class='white'>";
# =========================================================================================================
echo $forms->renderHeader('Museumsdaten');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('mu_id');
echo "<input type='hidden' name='mu_id' value='" . $neu['mu_id'] . "'";
# =========================================================================================================
echo $forms->renderTrenner('Museums- Ort');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('mu_name', 50);
echo $forms->renderTextLikeFieldFlex('mu_bezeichng', 50);
echo $forms->renderTextLikeFieldFlex('mu_adresse_a', 50);
echo $forms->renderTextLikeFieldFlex('mu_plz_a', 7);
echo $forms->renderTextLikeFieldFlex('mu_ort_a', 50);

# =========================================================================================================
echo $forms->renderTrenner('Post- Adresse, wenn anders als Standort:');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('mu_adresse_p', 50);
echo $forms->renderTextLikeFieldFlex('mu_plz_p', 7);
echo $forms->renderTextLikeFieldFlex('mu_ort_p', 50);

# =========================================================================================================
echo $forms->renderTrenner('Land, Bundesland');
# =========================================================================================================
$st = $t = "";
echo $forms->renderTextLikeFieldFlex('mu_staat', 50);
if (!$editProtect) {
    AutoCompForm_Staat();
}

$st = $neu['mu_staat'];
echo $forms->renderTextLikeFieldFlex('mu_bdland', 50);
if (!$editProtect) {
    AutoCompForm_Bdld('$st');
}


echo $forms->renderTextLikeFieldFlex('mu_eigner', 50);
if (!$editProtect) {
    #AutoCompForm_Eigent('E');
    AutoCompForm_Mandant();
}

echo $forms->renderSelectFieldFlex('mu_mustyp', VF_Mus_Typ);
$Et = array(
    "F" => "Feuerwehr",
    "P" => "Privat",
    "V" => "Verein"
);
echo $forms->renderSelectFieldFlex('mu_museigtyp', $Et);

$dataSetAct = false;
// accordion für Museums- Sammlung
echo "<ul id='ms-accordion' class='accordionjs' $dataSetAct >";
echo "<li>";
echo "<div>Sammlungs- Beschreibung - für Details anklicken</div>";
echo "<div>";

echo $forms->renderTextLikeFieldFlex('mu_sammelbeg', 4);
echo $forms->renderTextLikeFieldFlex('mu_sammlgschw', 200);
echo $forms->renderTextLikeFieldFlex('mu_besobj_1', 100);
echo $forms->renderTextLikeFieldFlex('mu_besobj_2', 100);
echo $forms->renderTextLikeFieldFlex('mu_besobj_3', 100);
echo $forms->renderTextLikeFieldFlex('mu_anz_obj', 4);

echo $forms->renderSelectFieldFlex('mu_archiv', VF_JN);
echo $forms->renderSelectFieldFlex('mu_protbuch', VF_JN);
echo $forms->renderSelectFieldFlex('mu_abzeich', VF_JN);
echo $forms->renderSelectFieldFlex('mu_ausruest', VF_JN);
echo $forms->renderSelectFieldFlex('mu_kleinger', VF_JN);
echo $forms->renderSelectFieldFlex('mu_grossger', VF_JN);
echo "</div>";
echo "</li>";
echo "</ul>";
// ende accordion für Museums- Sammlung

// accordion für Museums- Kustos
echo "<ul id='ku-accordion' class='accordionjs' $dataSetAct >";
echo "<li>";
echo "<div>Kustos - für Details anklicken</div>";
echo "<div>";
echo $forms->renderTextLikeFieldFlex('mu_kustos_titel', 10);
echo $forms->renderTextLikeFieldFlex('mu_kustos_vname', 35);
echo $forms->renderTextLikeFieldFlex('mu_kustos_name', 40);
echo $forms->renderTextLikeFieldFlex('mu_kustos_dgr', 10);
echo $forms->renderTextLikeFieldFlex('mu_kustos_tel', 35);
echo $forms->renderTextLikeFieldFlex('mu_kustos_intern', 50);
echo $forms->renderTextLikeFieldFlex('mu_kustos_email', 50);
echo "</div>";
echo "</li>";
echo "</ul>";
// ende accordion für Museums- Kustos

// accordion für Museums- Infrastruktur
echo "<ul id='in-accordion' class='accordionjs' $dataSetAct >";
echo "<li>";
echo "<div>Infrastruktur - für Details anklicken</div>";
echo "<div>";
echo $forms->renderSelectFieldFlex('mu_toilett', VF_JN);
echo $forms->renderSelectFieldFlex('mu_garderobe', VF_JN);
echo $forms->renderSelectFieldFlex('mu_cafe', VF_JN);
echo $forms->renderSelectFieldFlex('mu_rollstuhl', VF_JN);
echo $forms->renderTextLikeFieldFlex('mu_beheinr', 30);
echo $forms->renderTextLikeFieldFlex('mu_sonst_anb', 60);
echo "</div>";
echo "</li>";
echo "</ul>";
// ende accordion für Museums- Infrastruktur

// accordion für Museums- Öffnungszeiten
echo "<ul id='oe-accordion' class='accordionjs' $dataSetAct >";
echo "<li>";
echo "<div>Öffnungszeiten - für Details anklicken</div>";
echo "<div>";
$Oo = array(
    "G" => "Ganzjährig",
    "S" => "Saisonal",
    "V" => "nur nach Vereinbarung"
);
echo $forms->renderSelectFieldFlex('mu_oeffnung', $Oo);
echo $forms->renderTextLikeFieldFlex('mu_saison', 30);
echo $forms->renderTextLikeFieldFlex('mu_oez_mo', 30);
echo $forms->renderTextLikeFieldFlex('mu_oez_di', 30);
echo $forms->renderTextLikeFieldFlex('mu_oez_mi', 30);
echo $forms->renderTextLikeFieldFlex('mu_oez_do', 30);
echo $forms->renderTextLikeFieldFlex('mu_oez_fr', 30);
echo $forms->renderTextLikeFieldFlex('mu_oez_sa', 30);
echo $forms->renderTextLikeFieldFlex('mu_oez_so', 30);
echo $forms->renderTextLikeFieldFlex('mu_oez_fei', 30);
echo "</div>";
echo "</li>";
echo "</ul>";
// ende accordion für Museums- Öffnungszeiten

// accordion für Museums- Führungen
echo "<ul id='fu-accordion' class='accordionjs' $dataSetAct >"; 
echo "<li>";
echo "<div>Führungen, Auskunft - für Details anklicken</div>";
echo "<div>";
echo $forms->renderTextLikeFieldFlex('mu_f1_titel', 20);
echo $forms->renderTextLikeFieldFlex('mu_f1_vname', 35);
echo $forms->renderTextLikeFieldFlex('mu_f1_name', 35);
echo $forms->renderTextLikeFieldFlex('mu_f1_dgr', 10);
echo $forms->renderTextLikeFieldFlex('mu_f1_tel', 20);
echo $forms->renderTextLikeFieldFlex('mu_f1_email', 45);

echo $forms->renderTextLikeFieldFlex('mu_f2_titel', 20);
echo $forms->renderTextLikeFieldFlex('mu_f2_vname', 35);
echo $forms->renderTextLikeFieldFlex('mu_f2_name', 35);
echo $forms->renderTextLikeFieldFlex('mu_f2_dgr', 10);
echo $forms->renderTextLikeFieldFlex('mu_f2_tel', 20);
echo $forms->renderTextLikeFieldFlex('mu_f2_email', 45);
echo "</div>";
echo "</li>";
echo "</ul>";
// ende accordion für Museums- Führungen

# =========================================================================================================
$checkbox_f = "";

if ($hide_area == 1 && !$editProtect) {  //toggle??    && $accUpd == 0
   # $checked_f = 'checked';
    $checkbox_f = "<a href='#' class='toggle-string' data-toggle-group='1'>Foto Daten eingeben/ändern</a>";
}
echo $forms->renderTrenner('Fotos',$checkbox_f);  #
# =========================================================================================================
echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";
$pict_path = $path2ROOT."login/AOrd_Verz/Museen/";

echo "<input type='hidden' name='mu_bild_1' value='" . $neu['mu_bild_1'] . "'>";
echo "<input type='hidden' name='mu_bild_2' value='" . $neu['mu_bild_2'] . "'>";

echo "<input type='hidden' id='urhNr' value=''>";
echo "<input type='hidden' id='aOrd' value=''>";

echo "<input type='hidden' id='reSize' value='800'>";

$Feldlaenge = "200px";

$_SESSION[$module]['Pct_Arr' ] = array();
$num_foto = 2;
$i = 1;
while ($i <= $num_foto) {
    $_SESSION[$module]['Pct_Arr' ][] = array('udir' => $pict_path, 'ko' => 'mu_bildbeschr_'.$i, 'bi' => 'mu_bild_'.$i, 'rb' => '', 'up_err' => '','f1' => '','f2' => '');
    
    echo "<input type='hidden' id='aOrd_$i' value='Museen/'>";
    $i++;
}

UploadForm_M();

# =========================================================================================================

echo $forms->renderTrenner('Letzte Änderung ');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('mu_changed_id');
echo $forms->renderTextLikeFieldFlex('mu_changed_at');

# =========================================================================================================

if ($Zugr != "Alle" && userBerechtigtOK($Zugr)) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='MuseenList.php'>Zurück zur Liste</a></p>";

echo "</div>";

/** notwendige css und js einfügen */
echo "<script src='" . $path2ROOT . "login/Core/AllgVerw/js/AutoComp_Staat.js' ></script>";
echo "<script src='" . $path2ROOT . "login/Core/AllgVerw/js/AutoComp_Bdld.js' ></script>";
echo "<script src='" . $path2ROOT . "login/Core/Modules/Mandanten/js/AutoComp_Mandant.js' ></script>";
echo "<script src='" . $path2ROOT . "VFH/js/accordion.min.js' async></script>";
echo "<script src='" . $path2ROOT . "VFH/js/add_accordion.js' async></script>";
echo "<script src='" . $path2ROOT . "VFH/js/VF_toggle.js' async></script>";
echo "<script src='" . $path2ROOT . "VFH/js/VF_Foto_Upl.js' async></script>";
# echo "<script src='" . $path2ROOT . "VFH/js/VF_selFotoLibs.js' async></script>";
# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>MuseenEdit_ph0_inc.php beendet</pre>";
}
?>