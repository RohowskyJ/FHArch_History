<?php
/**
 * Anmeldung eines neuen Mitgliedes, Formular
 *
 * @author Josef Rohowsky - neu 2018
 *
 *
 */
if ($debug) {
    echo "<pre class=debug>MitglAnmeld_ph0.inc.php ist gestarted</pre>";
}

use Fharch\Core\Services\FormRendererFlex;

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";
/* nicht für dieses Script, muss von extern editieren konnen 
if (isset($_SESSION['BS_Prim']['Mod']) && $_SESSION['BS_Prim']['Mod']['smod'] != 'IntStart' ) {
    # $editProtect = true;
    # $readonly = false;
}
*/
$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

$ed_lcnt = 0;

echo $forms->renderHeader('Mitgliedsanmeldung');
# =========================================================================================================
# var_dump($neu);
echo "<strong>Ich </strong>";
echo "<p><strong>trete dem Verein \"Feuerwehrhistoriker in Niederösterreich\" bei und akzeptiere </strong>";
echo "<strong>die Statuten </strong>(werden auf Wunsch zugesandt). <strong>Den Mitgliedsbeitrag von " . $miBeitr . ".-€ pro Jahr werde ich bezahlen (UM-Unterstützendes Mitglied und FG-Sachbearbeiter FG).</strong></p>";

# =========================================================================================================
echo $forms->renderTrenner('Persönliche Daten');
# =========================================================================================================
if ($err != "") {
    echo "<br><b style='color:red;background:white;'>$err</b><br>";
}
echo "<input type='hidden' name='mi_id' value='" . $neu['mi_id'] . "' />";
echo "<input type='hidden' name='mi_neu_id' value='" . $neu['mi_neu_id'] . "' />";
echo "<input type='hidden' name='mi_neu_chkd' value='" . $neu['mi_neu_chkd'] . "' />";

echo $forms->renderRadioFieldFlex('mi_anrede', array(
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
AutoCompForm_Staat();

echo $forms->renderTextLikeFieldFlex('mi_anschr', 50, '', 'required');
echo $forms->renderTextLikeFieldFlex('mi_plz', 7, '', 'required');
echo $forms->renderTextLikeFieldFlex('mi_ort', 50, '', 'required');

echo $forms->renderTextLikeFieldFlex('mi_tel_handy', 100, '', 'required');

echo $forms->renderTextLikeFieldFlex('mi_fax', 50);
if ($mail_err != "") {
    echo "<br><b style='color:red;background:white;'>$mail_err</b><br>";
}
echo $forms->renderTextLikeFieldFlex('mi_email', 50, '', 'required');

# =========================================================================================================
echo $forms->renderTrenner('Organisations- Daten');
# =========================================================================================================

echo $forms->renderRadioFieldFlex('mi_mtyp', M_Typ);

echo $forms->renderSelectFieldFlex('mi_org_typ', M_Org);
echo $forms->renderTextLikeFieldFlex('mi_org_name', 50);

# =========================================================================================================
echo $forms->renderTrenner('Wo sind meine Interessen: ');
# =========================================================================================================

echo "<input type='hidden' name='mi_ref_int_2' value='" . $neu['mi_ref_int_2'] . "' />";
echo "<input type='hidden' name='mi_ref_int_3' value='" . $neu['mi_ref_int_3'] . "' />";
echo "<input type='hidden' name='mi_ref_int_4' value='" . $neu['mi_ref_int_4'] . "' />";

echo $forms->renderCheckboxFieldFlex('mi_ref_int_2', VF_Referate_anmeld[2]);
echo $forms->renderCheckboxFieldFlex('mi_ref_int_3', VF_Referate_anmeld[3]);
echo $forms->renderCheckboxFieldFlex('mi_ref_int_4', VF_Referate_anmeld[4]);

# =========================================================================================================
echo $forms->renderTrenner('Datenschutz- Erklärung (DSVGO)');
# =========================================================================================================

echo "  <div class='w3-container w3-blue'   ";

echo '      <p align="center">                                                                                                                                      ';
echo '        <font size="4" color="whitew"><b>Einwilligungserklärung im Sinne der EU Datenschutzgrundverordnung: <b/><br/></font>';
echo '         <font size="2" color="white">Sie (als AntragstellerIn) stimmen ausdrücklich zu, dass Ihre soeben erhobenen persönlichen Daten, nämlich Name, Adresse, e-Mail-Adresse, Telefonnummer, Geburtsdatum  ';
echo '         zum Zweck der elektronischen Zusendung von Vereinsnachrichten, Veranstaltungseinladungen, Anmeldebestätigungen, Zahlungsaufforderungen, Erinnerungen, und postalischer Zusendung von bestelltem Material bei erfolgtem Beitritt im Verein gespeichert, verwaltet und verarbeitet werden.<br /> ';
echo '         Sie können jederzeit Ihre bei uns gespeicherten Daten überprüfen und ändern lassen. Wenn Sie die Löschung Ihrer Daten bei uns beantragen, werden die Daten gelöscht und Ihre Vereinsmitgliedschaft erlischt automatisch. ';
echo '         </b></font></p>   ';

echo '<font size="+1">Ich willige in die Speicherung und Verarbeitung meiner persönlichen Daten ein: <br/></font> ';
echo "                  <input name=\"einverkl\" value=\"Y\" tabindex=\"33\" type=\"radio\"        required           /> &nbsp; JA  &nbsp; &nbsp; &nbsp; &nbsp;     ";
echo "                  <input name=\"einverkl\" value=\"N\" tabindex=\"34\" type=\"radio\" checked='checked' />  &nbsp; Nein   <br/>  ";

echo '</div> ';

echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";

echo "<p><a href='../'>Zurück zum Index (ABBRUCH der Anmeldung!)</a></p>";

# =========================================================================================================

if ($debug) {
    echo "<pre class=debug>MitglAnmeld_ph0.inc.php beendet</pre>";
}
?>