<?php
/**
 * Anmeldung eines neuen Mitgliedes, Formular (öffentlich, ohne Login)
 *
 * Erwartet aus MitglAnmeld.php: $meta, $neu, $Err_msg, $err, $mail_err, $miBeitr, $module, $basePath, $pfg, $contact, $debug
 *
 * @author Josef Rohowsky - neu 2018, überarbeitet 2026
 */
if ($debug) {
    echo "<pre class=debug>MitglAnmeld_ph0_inc.php ist gestarted</pre>";
}

use FWHist\Services\FormRendererFlex_Ext;

$editProtect = false;  // öffentliche Anmeldung: immer bearbeitbar

// FormRendererFlex_Ext: wie FormRendererFlex, dazu die Autocomplete-Bausteine (renderAutoComp*)
$forms = new FormRendererFlex_Ext($meta, 0, $neu, $Err_msg, $editProtect, $module );

$h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

echo $forms->renderHeader('Mitgliedsanmeldung');
# =========================================================================================================
echo "<strong>Ich </strong>";
echo "<p><strong>trete dem Verein \"Feuerwehrhistoriker in Niederösterreich\" bei und akzeptiere </strong>";
echo "<strong>die Statuten </strong>(werden auf Wunsch zugesandt).";
if ($miBeitr !== '') {
    echo " <strong>Den Mitgliedsbeitrag von " . $h((string) $miBeitr) . ".-€ pro Jahr werde ich bezahlen (UM-Unterstützendes Mitglied und FG-Sachbearbeiter FG).</strong>";
}
echo "</p>";

# =========================================================================================================
echo $forms->renderTrenner('Persönliche Daten');
# =========================================================================================================
if ($err !== "") {
    echo "<div role='alert' style='margin:1em 0;padding:.8em;border:3px solid #b00;background:#fff0f0;color:#900;font-size:1.15em'><b>" . $h($err) . "</b></div>";
}

echo $forms->renderSelectFieldFlex('mi_anrede', array(
    "Fr." => "Frau ",
    "Hr." => "Herr "
    ), '', 'required');
echo $forms->renderTextLikeFieldFlex('mi_titel', 10, 'Akad. Titel');
echo $forms->renderTextLikeFieldFlex('mi_name', 50, '', 'required');
echo $forms->renderTextLikeFieldFlex('mi_vname', 50, '', 'required');

echo $forms->renderTextLikeFieldFlex('mi_n_titel', 10, 'Titel, nachgestellt');
echo $forms->renderTextLikeFieldFlex('mi_dgr', 10, 'FF Dienstgrad');

echo $forms->renderTextLikeFieldFlex('mi_gebtag', 10, '', 'type="date" required');

echo $forms->renderTextLikeFieldFlex('mi_staat', 0);   // , $neu['st_name']
echo $forms->renderAutoCompStaat();

echo $forms->renderTextLikeFieldFlex('mi_anschr', 50, '', 'required');
echo $forms->renderTextLikeFieldFlex('mi_plz', 7, '', 'required');
echo $forms->renderTextLikeFieldFlex('mi_ort', 50, '', 'required');

echo $forms->renderTextLikeFieldFlex('mi_tel_handy', 100, '', 'required');

echo $forms->renderTextLikeFieldFlex('mi_fax', 50);
echo $forms->renderTextLikeFieldFlex('mi_email', 50, '', 'required');

# =========================================================================================================
echo $forms->renderTrenner('Organisations- Daten');
# =========================================================================================================

echo $forms->renderSelectFieldFlex('mi_mtyp', M_Typ);

echo $forms->renderSelectFieldFlex('mi_org_typ', M_Org);
echo $forms->renderTextLikeFieldFlex('mi_org_name', 50);

# =========================================================================================================
echo $forms->renderTrenner('Wo sind meine Interessen: ');
# =========================================================================================================

echo "<input type='hidden' name='mi_ref_int_2' value='" . $h((string) $neu['mi_ref_int_2']) . "' />";
echo "<input type='hidden' name='mi_ref_int_3' value='" . $h((string) $neu['mi_ref_int_3']) . "' />";
echo "<input type='hidden' name='mi_ref_int_4' value='" . $h((string) $neu['mi_ref_int_4']) . "' />";

echo $forms->renderCheckboxFieldFlex('mi_ref_int_2', VF_Referate_anmeld[2]);
echo $forms->renderCheckboxFieldFlex('mi_ref_int_3', VF_Referate_anmeld[3]);
echo $forms->renderCheckboxFieldFlex('mi_ref_int_4', VF_Referate_anmeld[4]);

# =========================================================================================================
echo $forms->renderTrenner('Datenschutz- Erklärung (DSVGO)');
# =========================================================================================================

echo "<div class='w3-container w3-blue' style='padding:1em'>";
echo '<p style="text-align:center;font-size:1.2em"><b>Einwilligungserklärung im Sinne der EU Datenschutzgrundverordnung:</b></p>';
echo '<p style="font-size:1em">Sie (als AntragstellerIn) stimmen ausdrücklich zu, dass Ihre soeben erhobenen persönlichen Daten, nämlich Name, Adresse, e-Mail-Adresse, Telefonnummer, Geburtsdatum ';
echo 'zum Zweck der elektronischen Zusendung von Vereinsnachrichten, Veranstaltungseinladungen, Anmeldebestätigungen, Zahlungsaufforderungen, Erinnerungen, und postalischer Zusendung von bestelltem Material bei erfolgtem Beitritt im Verein gespeichert, verwaltet und verarbeitet werden.<br /> ';
echo 'Sie können jederzeit Ihre bei uns gespeicherten Daten überprüfen und ändern lassen. Wenn Sie die Löschung Ihrer Daten bei uns beantragen, werden die Daten gelöscht und Ihre Vereinsmitgliedschaft erlischt automatisch.</p>';

if (!empty($Err_msg['einverkl'])) {
    echo "<p role='alert' style='background:#fff;color:#900;padding:.5em;font-weight:bold'>" . $h((string) $Err_msg['einverkl']) . "</p>";
}
echo '<p style="font-size:1.2em">Ich willige in die Speicherung und Verarbeitung meiner persönlichen Daten ein:<br/>';
echo "<input id='einverkl_y' name=\"einverkl\" value=\"Y\" tabindex=\"33\" type=\"radio\" required /> <label for='einverkl_y'>JA</label> &nbsp; &nbsp; &nbsp; &nbsp; ";
echo "<input id='einverkl_n' name=\"einverkl\" value=\"N\" tabindex=\"34\" type=\"radio\" checked='checked' /> <label for='einverkl_n'>Nein</label></p>";
echo '</div> ';

# =========================================================================================================
echo $forms->renderTrenner('Sicherheitsabfrage');
# =========================================================================================================
echo $pfg->renderFields($contact);   // CSRF-Token, Honigtopf, Nummern-Captcha

echo "<p>Nach Eingabe aller Daten drücken Sie ";
echo "<button type='submit' name='phase' value='1' class=green style='font-size:1.2em;padding:.5em 1.2em'>Anmeldung absenden</button></p>";

echo "<p><a href='../'>Zurück zum Index (ABBRUCH der Anmeldung!)</a></p>";

# =========================================================================================================

/** notwendige css und js einfügen */
echo "<script src='" . $h($basePath . "/FWHist/login/AllgVerw/js/AutoComp_Staat.js") . "' ></script>";

if ($debug) {
    echo "<pre class=debug>MitglAnmeld_ph0_inc.php beendet</pre>";
}
?>
