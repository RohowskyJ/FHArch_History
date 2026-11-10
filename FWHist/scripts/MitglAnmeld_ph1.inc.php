<?php

/**
 * Anmeldung eines neuen Mitgliedes, Abspeichern Datensatzsatz . 
 *
 * @author Josef Rohowsky - neu 2018
 *
 * Datensatz schreiben, Log schreiben, E-Mail an Anmelder, Aufruf zur Erstellung Eigentümer und Benutzer.
 * 
 * 
 */

if ($debug) {
    echo "<pre class=debug>MitglAnmeld_ph1.inc.php ist gestarted</pre>";
}

if ($neu['staat'] != '' || $neu['staat_id'] != '' ) {
    if ($neu['staat_id'] != '' ) {
        $neu['mi_staat'] = $neu['staat_id'];
    }
}

unset($neu['mi_neu_id']);
unset($neu['einverkl']);
unset($neu['staat']);
unset($neu['staat_id']);
unset($neu['phase']);

/** Token für dem E-Mail-Check */
$neu['mi_neu_token'] = bin2hex(random_bytes(16));

$neu['mi_changed_id'] = 999999;
$neu['mi_neu_tok_erz'] = date("Y-m-d H:i:s");
$neu['mi_changed_at']  = date("Y-m-d H:i:s");
#console_log(__LINE__);
$neu['mi_neu_nr']  = $mitgl->createMiAnmeldg($neu); 
console_log(__LINE__ ." " . $neu['mi_neu_nr']);
$datum = date("d.m.Y:");
$zeit = date("H:i:s");

$dsn = $systemBasePath . "login/AOrd_Verz/Logging/logs/AnmeldLog";

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
$log_rec .= "Fax: " . $neu['mi_fax'] . "\n";
$log_rec .= "Geburtsdatum: " . $neu['mi_gebtag'] . "\n";
$log_rec .= "Oganisationstyp: " . $neu['mi_org_typ'] . "\n";
$log_rec .= "Organisation: " . $neu['mi_org_name'] . "\n";
# $log_rec .= "Referatsfunktion: ".$neu['mi_ref_int']."\n";
# $log_rec .= "Referatsmitarbeit: ".$neu['mi_ref_ma']."\n";
# $log_rec .= "Referatsinormation: ".$neu['mi_ref_int']."\n";
$log_rec .= "Einverstaendniserklaerung: " . $neu['mi_einversterkl'] . " $datum $zeit " . $neu['mi_einv_art'] . "\n";
$log_rec .= "Mitglieds- Vormerknummer:  " . $neu['mi_neu_nr'] . "\n";
$text = " $log_rec ***** \"LOG ENDE\" *****\n";
$text .= "Orig.TCP = " . $_SERVER['REMOTE_ADDR'] . "\n";

$fname = writelog($dsn, $text);
$tr = array(
    "\n" => "<br>"
);
$text = strtr($text, $tr);

$adr_list = (new \FWHist\AllgVerw\API\AdminMailAddressRepository($pdo))
    ->getActiveAddressesByGroup('Mitgl');
echo __LINE__ . " $adr_list , josef@kexi.at  ,  VFHNÖ Mitglieds- Neuanmeldung 1. Teil , $text <br>";
sendEMail("$adr_list , josef@kexi.at", "VFHNÖ Mitglieds- Neuanmeldung 1. Teil", $text); # service@feuerwehrhistoriker.at, helmut-riegler@aon.at"

$c_email = "";
/** falls keine Zusatzmail definiert wurde, an die Default Mail- Adresse schicken */
if ($adr_list == '') {
    // Mail an den SysAdmin
    $c_email = $_SESSION['BS_Prim']['vema'];
}

$anr = 'Werte Kameradin, Frau ';
if ($neu['mi_anrede'] == 'Hr.') {
    $anr = 'Werter Kamerad, Herr ';
}

// Aufbauen des Links
$link = getBaseUrlToFWHist() .  '/login/Modules/Mitglieder/MitglRegBestaetigung.php?token=' . $neu['mi_neu_token'];

// Standard- Nachricht

$text = $anr . $neu['mi_titel'] . " " . $neu['mi_vname'] . " " . $neu['mi_name'] . " " . $neu['mi_n_titel'] . ", ";
$text .= "
<p>Die Daten sind vorläufig abgespeichert. </p>
<p>Bitte zum Bestätigen der E-Mail- Adresse <a href='" . $link . "' >diesen Link anklicken</a> um die Anmeldung abzuschliessen</span>.</p>
<p>Danach kommt eine neue E-Mail mit der Bestätigung der Anmeldung. Danach ist die der interne Bereich der Home-Page nutzbar.</p>
";

sendEmail("$adr_list, josef@kexi.at", // Empänger(Liste)
    "Neuanmeldung 1. Teil " . $neu['mi_name'] . " ".$neu['mi_vname'], // Subject Text der EMail
      $text, // Inhalt der Email in HTML format
      $c_email); 

echo  "<div class='Menu-Header'>Neu- Anmeldung  -  Hinweis</div>";
   
?>

Werter Kamerad, Werte Kameradin,
<?php 
echo $anr . $neu['mi_titel'] . " " . $neu['mi_vname'] . " " . $neu['mi_name'] . " " . $neu['mi_n_titel'] . ", ";
?>
<p>Die Daten sind abgespeichert. In Kürze bekommst Du eine E-Mail an die angegebene Adresse. 
Bitte den dortigen Link 
<span style='color:blue;'>
um die Anmeldung abzuschliessen</span> 
anklicken, damit die Anmeldung nach der Adressprüfung abgeschlossen wird.</p>
<p>Danach kommt eine neue E-Mail mit der Bestätigung der Anmeldung. Danach ist die der interne Bereich der Home-Page nutzbar.</p>

<a href='<?php echo $basePath ?>/FWHist'>Zurück zur Home Page</a>
<?php 

if ($debug) {
    echo "<pre class=debug>MitglAnmeld_ph1.inc.php beendet</pre>";
}

?>