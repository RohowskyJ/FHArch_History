<?php


/**
 * Museums- Daten- Wartung, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 * 
 */
use FWHist\Services\FormRendererFlex_Ext;
use FWHist\Services\API\AutoCompleteAPI;


if ($debug) {
    echo "<pre class=debug>MuseenEdit_ph0_inc.php ist gestarted</pre>";
}


$editProtect = !$canEdit;
$readonly = "";

$newRec = false; // Markierung für Anlegen: Upload. Functionen immer anzeigen, wenn  true, sonst verstecken, nur Anzeige

if ((int)$neu['mu_id'] !== 0) {
    $readonly = 'readonly';
    $newRec = $canEdit;
}

$forms = new FormRendererFlex_Ext($meta, $phase,  $neu, [], $editProtect, $module );
if ($canEdit) {
    echo \FWHist\Auth\AccessGuard::csrfField();
}

/** Werte für Foto Mgmt und initialen Zustand der Detailbereiche */
$isNewRecord = (int)$neu['mu_id'] === 0;
if ($isNewRecord) {
    $hide_area = 0;
} else {
    $hide_area = 1;
}
$blindOpen = $isNewRecord ? ' open' : '';

/** Eingaben für weitere Scripts */
echo "<input type='hidden' id='recId' value='" . $neu['mu_id'] . " >";
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
echo $forms->renderTextLikeFieldFlex('mu_staat', 50);
if (!$editProtect) {
    echo $forms->AutoCompStaat();
}

echo $forms->renderTextLikeFieldFlex('mu_bdland', 50);
if (!$editProtect) {
    echo $forms->AutoCompBdld();
}

echo $forms->renderTextLikeFieldFlex('mu_eigner', 50);
if (!$editProtect) {
    echo $forms->AutoCompMandant('toggle', 'mandant', 'mandant');
}

echo $forms->renderSelectFieldFlex('mu_mustyp', VF_Mus_Typ);
$Et = array(
    "F" => "Feuerwehr",
    "P" => "Privat",
    "V" => "Verein"
);
echo $forms->renderSelectFieldFlex('mu_museigtyp', $Et);

// Details für Museums-Sammlung
echo "<details class='museum-blind'$blindOpen>";
echo "<summary>Sammlungs-Beschreibung</summary>";
echo "<div class='museum-blind-content'>";

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
echo "</details>";

// Details für Museums-Kustos
echo "<details class='museum-blind'$blindOpen>";
echo "<summary>Kustos</summary>";
echo "<div class='museum-blind-content'>";
echo $forms->renderTextLikeFieldFlex('mu_kustos_titel', 10);
echo $forms->renderTextLikeFieldFlex('mu_kustos_vname', 35);
echo $forms->renderTextLikeFieldFlex('mu_kustos_name', 40);
echo $forms->renderTextLikeFieldFlex('mu_kustos_dgr', 10);
echo $forms->renderTextLikeFieldFlex('mu_kustos_tel', 35);
echo $forms->renderTextLikeFieldFlex('mu_kustos_intern', 50);
echo $forms->renderTextLikeFieldFlex('mu_kustos_email', 50);
echo "</div>";
echo "</details>";

// Details für Museums-Infrastruktur
echo "<details class='museum-blind'$blindOpen>";
echo "<summary>Infrastruktur</summary>";
echo "<div class='museum-blind-content'>";
echo $forms->renderSelectFieldFlex('mu_toilett', VF_JN);
echo $forms->renderSelectFieldFlex('mu_garderobe', VF_JN);
echo $forms->renderSelectFieldFlex('mu_cafe', VF_JN);
echo $forms->renderSelectFieldFlex('mu_rollstuhl', VF_JN);
echo $forms->renderTextLikeFieldFlex('mu_beheinr', 30);
echo $forms->renderTextLikeFieldFlex('mu_sonst_anb', 60);
echo "</div>";
echo "</details>";

// Details für Museums-Öffnungszeiten
echo "<details class='museum-blind'$blindOpen>";
echo "<summary>Öffnungszeiten</summary>";
echo "<div class='museum-blind-content'>";
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
echo "</details>";

// Details für Museums-Führungen
echo "<details class='museum-blind'$blindOpen>";
echo "<summary>Führungen, Auskunft</summary>";
echo "<div class='museum-blind-content'>";
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
echo "</details>";

# =========================================================================================================
echo $forms->renderTrenner('Fotos');  #
# =========================================================================================================
echo "<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";
$pict_path = $basePath."login/AOrd_Verz/Museen/";

echo "<input type='hidden' name='mu_bild_1' value='" . $neu['mu_bild_1'] . "'>";
echo "<input type='hidden' name='mu_bild_2' value='" . $neu['mu_bild_2'] . "'>";

echo "<input type='hidden' id='urhNr' value=''>";
echo "<input type='hidden' id='aOrd' value=''>";

/** Variablen zur Fehlervermeidung */
$neu['ei_id'] = 9;
$neu['ad_sg'] = $neu['ad_sub_sg'] = "";

$pictPath = $basePath . "/FWHist/login/AOrd_Verz/Museen/";

$Feldlaenge = "100px";

$pic_cnt = 2;

$i = 1;
while ($i <= $pic_cnt) {
    $pct_arr[] = array('udir' => $pictPath, 'ko' => '', 'bi' => 'mu_bild_'.$i , 'rb' => '', 'up_err' => '','f1'=>'','f2'=>'');
    $i++;
}

Upload_Form($pic_cnt, $pct_arr, $newRec);


# =========================================================================================================

echo $forms->renderTrenner('Letzte Änderung ');
# =========================================================================================================
echo $forms->renderTextLikeFieldFlex('mu_changed_id');
echo $forms->renderTextLikeFieldFlex('mu_changed_at');

# =========================================================================================================

if ($canEdit) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
}

echo "<p><a href='MuseenList.php'>Zurück zur Liste</a></p>";

echo "</div>";

/** notwendige css und js einfügen */
echo "<style>
    .museum-blind {
        margin: .6rem 0;
        border: 1px solid #d2d2d2;
    }
    .museum-blind > summary {
        padding: .65rem .8rem;
        background: #f2f2f2;
        cursor: pointer;
        font-weight: 600;
    }
    .museum-blind[open] > summary {
        background: #f9d9bd;
    }
    .museum-blind > summary:focus-visible {
        outline: 2px solid #555;
        outline-offset: 2px;
    }
    .museum-blind-content {
        padding: .5rem;
    }
</style>";

echo "<script src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Staat.js'></script>";
echo "<script src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Bdld.js'></script>";
echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js' ></script>";
echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Urheber.js' ></script>";


# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>MuseenEdit_ph0_inc.php beendet</pre>";
}
?>