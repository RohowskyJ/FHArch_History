<?php

/**
 * Mitgliederverwaltung, Date abspeichern
 *
 * @author Josef Rohowsky - neu 2020
 *
 */
# var_dump($_POST);


if ($debug) {
    echo "<pre class=debug>MitglEdit_ph1.inc.php ist gestarted</pre>";
}
#var_dump($neu);
if ($debug) {
    echo '<pre class=debug>';
    echo '<hr>$neu: ';
    var_dump($neu);
    echo '<hr>$_SESSION[$module] : ';
    print_r($_SESSION[$module]);
    echo '</pre>';
}
$p_uid = $_SESSION['BS_Prim']['BE']['be_id'];
#echo "L 021 sterbdat ".$neu['mi_sterbdat']."<br>";
if ($neu['mi_sterbdat'] > "0000-00-00") {
   echo "Replace E-Mail Addr und Tel mit blank <br>";   
}

if ($neu['staat_id']) {
    $neu['mi_staat'] = $neu['staat_id'];
}
if ($neu['mi_sterbdat'] == "") {
    $neu['mi_sterbdat'] = NULL;
}
if ($neu['mi_austrdat'] == '') {
    $neu['mi_austrdat'] = NULL;
}
if ($neu['mi_m_beitr_bez'] == "") {
    $neu['mi_m_beitr_bez'] = NULL;
}
if ($neu['mi_m_abo_bez'] == "") {
    $neu['mi_m_abo_bez'] = NULL;
}

unset($neu['phase']);
unset($neu['list_ID']);
unset($neu['staat']);
unset($neu['staat_id']);

$neu['mi_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
$id = $neu['mi_id'];

#var_dump($neu);

if ($neu['mi_id'] == 0) { // Neu anlegen eines Mitglieds- Datensatzes
    
    try {
        $neu['mi_insert_id'] = $insert_id = $mitgl->createMitglied($neu);
        var_dump($result);
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage();
    }
    
#    var_dump($insert_id);
    ob_flush();
    
    
    $datum = date("d.m.Y:");
    $zeit = date("H:i:s");

    $dsn = "../login/logs/anmeldlog";

    $log_rec = "**** PFLICHTFELDER **** \nAnrede: " . $neu['mi_anrede'] . "\n";
    $log_rec .= "Familienname: " . $neu['mi_name'] . "\n";
    $log_rec .= "Vorname: " . $neu['mi_vname'] . "\n";
    $log_rec .= "E-Mail: " . $neu['mi_email'] . "\n";
    $log_rec .= "Adresse: " . $neu['mi_anschr'] . "\n";
    $log_rec .= "PLZ: " . $neu['mi_plz'] . "\n";
    $log_rec .= "Ort: " . $neu['mi_ort'] . "\n";

    // ******** Optionale Felder ********
    $log_rec .= "**** Optionale Felder ****\nTitel: " . $neu['mi_titel'] . "\n";
    $log_rec .= "**** Optionale Felder ****\n nachfolg. Titel: " . $neu['mi_n_titel'] . "\n";

    $log_rec .= "Tel Nummmer: " . $neu['mi_tel_handy'] . "\n";
    $log_rec .= "Mobil Nummer: " . $neu['mi_handy'] . "\n";
    $log_rec .= "Fax: " . $neu['mi_fax'] . "\n";
    $log_rec .= "Geburtsdatum: " . $neu['mi_gebtag'] . "\n";
    $log_rec .= "Oganisationstyp: " . $neu['mi_org_typ'] . "\n";
    $log_rec .= "Organisation: " . $neu['mi_org_name'] . "\n";
    # $log_rec .= "Referatsfunktion: ".$neu['mi_ref_int']."\n";
    # $log_rec .= "Referatsmitarbeit: ".$neu['mi_ref_ma']."\n";
    # $log_rec .= "Referatsinormation: ".$neu['mi_ref_int']."\n";
    $log_rec .= "Einverstaendniserklaerung: " . $neu['mi_einversterkl'] . " $datum $zeit " . $neu['mi_einv_art'] . "\n";
    $log_rec .= "Mitgliedsnummer:  " . $_SESSION['neu_mitgl']['neu_mi_id'] . "\n";
    $text = " $log_rec ***** \"LOG ENDE\" *****\n";
    $text .= "Orig.TCP = " . $_SERVER['REMOTE_ADDR'] . "\n";

    $fname = writelog($dsn, $text);
    $tr = array(
        "\n" => "<br>"
    );
    $text = strtr($text, $tr);

    $adr_list = VF_Mail_Set('Mitgl');

    if ($module == "0_EM") {
        sendEMail($neu['mi_email'] . ", $adr_list , josef@kexi.at", "VFHNÖ Mitglieds- Neuanmeldung ", $text); # service@feuerwehrhistoriker.at, helmut-riegler@aon.at, f.blueml@gmx.at"
    }

    $text = "Zur Info: Soeben " . $datum . " / " . $zeit . " wurde eine Anmeldung online  dem System übergeben.\n";
    $text .= "Im Formular wurden u.A. Name / Vorname / Emailadresse erfasst: " . $neu['mi_name'] . " / " . $neu['mi_vname'] . " / " . $neu['mi_email'] . ".\n";
    IF (! empty($ftext)) {
        $text .= "\nAchtung fehlende Pflichtangaben: $ftext\n\n";
    }
    $text .= "Weitere Infos sind im Anmeldelog ersichtlich.\n";
    $text .= "http://www.feuerwehrhistoriker.at/login/log/DSVGO_log/";
    $text .= "\nBitte beobachten ob die Anmeldung korrekt beendet wurde\n";
    $text .= "und zu einem Teilnehmer Aufnameantrag geführt hat!\n";
    $text .= "Zum Ansehen des Logs folgenden Link anklicken:\n";
    $text .= "http://www.feuerwehrhistoriker.at/login/log/dir.php\n";
    $text .= "Mitgliedsnummer:  " . $_SESSION['neu_mitgl']['neu_mi_id'] . "\n";
    $text .= "Anmeldelog  Mail Ende\n";

    sendEmail("$adr_list, josef@kexi.at", // Empänger(Liste)
    "Neuanmeldung " . $neu['mi_name'] . " ", // Subject Text der EMail
    $text, // Inhalt der Email in HTML format
    "service@feuerwehrhistoriker.at"); // optionale 'Reply-To' E-Mail-Adresse
    
    header ("Loction: /public/index.php");
} else { // ändern eines Mitglieds- Daensatzes
    
    try {
        $result = $mitgl->updateMitglied($id, $neu);
        #var_dump($result);
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage();
    }
    # $result = $mitgl->updateMitglied($id, $neu);
    #var_dump($result);
    ob_flush();
    $mi_neu_id = '';
    if (isset($_SESSION['neu_mitgl']['neu_mi_id'])) {
        $mi_neu_id = $_SESSION['neu_mitgl']['neu_mi_id'];
    }
    $logtext = "Änderungen in fv_mitglieder für " . $neu['mi_name'] . "  " . $neu['mi_vname'] . " " . $mi_neu_id . " \nMitgliedsdaten geändert oder neu angelegt von Benutzer $p_uid ";
    writelog($path2ROOT . "login/logs/MitglLog/Mitgl_aenderg_log", $logtext);
}

header ("Location: MitglList.php");
if ($debug) {
    echo "<pre class=debug>MitglEdit_ph1.inc.php beendet</pre>";
}
?>