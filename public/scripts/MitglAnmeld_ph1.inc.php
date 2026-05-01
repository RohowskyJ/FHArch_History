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

$dsn = $path2ROOT."data/AOrd_Verz/Logging/logs/AnmeldLog";

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

/** 
 * muss als pdo abfrage etabliert werden, am besten in AllgVerw
 * @var unknown $adr_list

$adr_list = VS_Mail_Set('Mitgl');
 */
$adr_list = 'josef@kexi.at';

sendEMail("$adr_list , josef@kexi.at", "VFHNÖ Mitglieds- Neuanmeldung 1. Teil", $text); # service@feuerwehrhistoriker.at, helmut-riegler@aon.at, f.blueml@gmx.at"
// das war die Admin- E-Mail

// Mail an den ANtragssteller
$ini_arr = parse_ini_file($path2ROOT.'login/common/config_s.ini',True,INI_SCANNER_NORMAL);
$c_email =$ini_arr['Config']['vema'];

$anr = 'Werte Kameradin, Frau ';
if ($neu['mi_anrede'] == 'Hr.') {
    $anr = 'Werter Kamerad, Herr ';
}

// Aufbauen des Links
$srv= $_SERVER['HTTP_HOST'];
$caller = $_SERVER['REQUEST_URI'];
$cal_arr = explode("/",$caller);
# var_dump($cal_arr);
$link = $srv . '/'.$cal_arr[1].'/src/Core/Modules/Mitglieder/MitglRegister.php?token=' . $neu['mi_neu_token'];
// Standard- Nachricht

$text = $anr . $neu['mi_titel'] . " " . $neu['mi_vname'] . " " . $neu['mi_name'] . " " . $neu['mi_n_titel'] . ", ";
$text .= "
<p>Die Daten sind vorläufig abgespeichert. </p>
<p>Bitte zum Bestätigen der E-Mail- Adresse <a href='http://" . $link . "' >diesen Link anklicken</a> um die Anmeldung abzuschliessen</span>.</p>
<p>Danach kommt eine neue E-Mail mit der Bestätigung der Anmeldung. Danach ist die der interne Bereich der Home-Page nutzbar.</p>
";

sendEmail("$adr_list, josef@kexi.at", // Empänger(Liste)
    "Neuanmeldung 1. Teil " . $neu['mi_name'] . " ".$neu['mi_vname'], // Subject Text der EMail
      $text, // Inhalt der Email in HTML format
      $c_email); // optionale 'Reply-To' E-Mail-Adresse

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

<!-- 

$text = "";
$msg = "";

if ($unguelt || $abgelaufen | !$bestaetigt) {
    if ($unguelt) { // ungültiger Token
        $subject = "Diese Anforderung ist ungültig - keine Daten vorhanden.";
        $msg = "<p>Bitte Anmeldung neu eingeben.</p>";
        echo "<p>Ungültiger Bestätigungscode - Abbruch. Bitte neu anmelden </p>";
        exit;
    } elseif ($abgelaufen){ // Bestätigun zu spaät (48 Stunden Frist)
        $subject = "Diese Anmeldeüberprüfung wurde zu spät abgeschickt.";
        $msg = "<p>Diese Anmeldeüberprüfung wurde nach mehr als 48 Stunden nach der Anmeldung gestarted. Bitte neu eingeben.</p>";
        echo "<p>Bestätingung wurde zu spät (nach mehr als 48 Stunden) abgeschickt - bitte neu anmelden </p>";
    } elseif ($bestaetigt) { // Anmeldung wurde bereits abgeschlossen
        $subject = "";
        $msg = "";
        echo "<p>Die Anmeldung wurde bereits abgeschlossen. Die Interne Seite ist bereits nutzbar </p>";
    } else {  // normaler Ablauf, Bestätigung Anmeldung und PW
        $subject = "Willkommen! Anmeldung Abgeschlossen";
        $msg = "<p>Die Anmeldung wurde damit abgeschlossen. Die interne Seite ist nun Nutzbar. Der Benutzer- Id ist die E-Mail- Adresse (exakt so geschrieben wie bei der Anmeldung)
              Das Passwort ist NeuBen$mi_id und es muss ehestmöglich geändert werden.</p>";
        echo "<p>Die Anmeldung wurde damit abgeschlossen. Die interne Seite ist nun Nutzbar. Der Benutzer- Id ist die E-Mail- Adresse (exakt so geschrieben wie bei der Anmeldung).</p>
              <p>Das Passwort ist NeuBen$mi_id und es muss ehestmöglich geändert werden.</p>
              <p>Die Mitglieds- Nummer ist $mi_id</p>.
              ";
        
    }
}


$text .= "<h2>Willkommen!</h2>";
$text .= $msg; //"";
$text .= "<p>Mit Kameradschaftlichen Grüßen, der Mitglieder- Verantwortliche.  $nPw </p>";
 -->
<a href='./public'>Zurück zur Home Page</a>
<?php 

if ($debug) {
    echo "<pre class=debug>MitglAnmeld_ph1.inc.php beendet</pre>";
}

?>