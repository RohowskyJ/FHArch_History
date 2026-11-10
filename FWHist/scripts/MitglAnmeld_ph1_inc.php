<?php

/**
 * Anmeldung eines neuen Mitgliedes, nach dem Abspeichern (öffentlich, ohne Login)
 *
 * @author Josef Rohowsky - neu 2018, überarbeitet 2026
 *
 * Der Datensatz ist in MitglAnmeld.php gespeichert ($neu = Datensatz inkl. mi_neu_nr und Token).
 * Hier: Log schreiben, Hinweis-E-Mail an die Verantwortlichen, Bestätigungs-E-Mail mit Link an den ANMELDER,
 * Bestätigungsseite. Alle Werte aus dem Formular werden für HTML-Mails escaped.
 */

if ($debug) {
    echo "<pre class=debug>MitglAnmeld_ph1_inc.php ist gestarted</pre>";
}

$e = static fn(mixed $s): string => htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$datum = date("d.m.Y:");
$zeit = date("H:i:s");

// ---- Log (Text) -------------------------------------------------------------------------------------------
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
$log_rec .= "Einverstaendniserklaerung: " . $neu['mi_einversterkl'] . " $datum $zeit " . $neu['mi_einv_art'] . "\n";
$log_rec .= "Mitglieds- Vormerknummer:  " . $neu['mi_neu_nr'] . "\n";
$text = " $log_rec ***** \"LOG ENDE\" *****\n";
$text .= "Orig.TCP = " . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n";

writelog($dsn, $text);

// ---- Empfänger der Hinweis-E-Mail: Gruppe "Mitgl" der administrativen Adressen, sonst allgemeine Vereinsadresse ---
$adr_list = (new \FWHist\AllgVerw\API\AdminMailAddressRepository($pdo))
    ->getActiveAddressesByGroup('Mitgl');
$adr_list = is_array($adr_list) ? implode(', ', $adr_list) : trim((string) $adr_list);
if ($adr_list === '') {
    $adr_list = trim((string) ($_SESSION['BS_Prim']['vema'] ?? ''));
}

// Hinweis an die Verantwortlichen (ohne Bestätigungs-Link)
if ($adr_list !== '') {
    $adminText = nl2br($e($text));
    try {
        sendEMail($adr_list, "VFHNÖ Mitglieds- Neuanmeldung 1. Teil", $adminText);
    } catch (Throwable $ex) {
        error_log('MitglAnmeld: Hinweis-E-Mail fehlgeschlagen: ' . $ex->getMessage());
    }
} else {
    error_log('MitglAnmeld: keine Empfänger für die Hinweis-E-Mail (Gruppe Mitgl leer, vema nicht gesetzt)');
}

// ---- Bestätigungs-E-Mail mit Link an den ANMELDER ---------------------------------------------------------------
$anr = 'Werte Kameradin, Frau ';
if ($neu['mi_anrede'] == 'Hr.') {
    $anr = 'Werter Kamerad, Herr ';
}

// Aufbauen des Links
$link = getBaseUrlToFWHist() .  '/login/Modules/Mitglieder/MitglRegBestaetigung.php?token=' . rawurlencode((string) $neu['mi_neu_token']);

$name = $e(trim($neu['mi_titel'] . " " . $neu['mi_vname'] . " " . $neu['mi_name'] . " " . $neu['mi_n_titel']));
$mailText = $anr . $name . ", ";
$mailText .= "
<p>Die Daten sind vorläufig abgespeichert. </p>
<p>Bitte zum Bestätigen der E-Mail- Adresse <a href='" . $e($link) . "' >diesen Link anklicken</a> um die Anmeldung abzuschliessen</span>.</p>
<p>Danach kommt eine neue E-Mail mit der Bestätigung der Anmeldung. Danach ist die der interne Bereich der Home-Page nutzbar.</p>
";

try {
    sendEmail((string) $neu['mi_email'],                                      // Empfänger: der Anmelder
        "Neuanmeldung 1. Teil " . $neu['mi_name'] . " " . $neu['mi_vname'],   // Subject Text der EMail
        $mailText);                                                           // Inhalt der Email in HTML format
} catch (Throwable $ex) {
    error_log('MitglAnmeld: Bestätigungs-E-Mail fehlgeschlagen: ' . $ex->getMessage());
}

echo  "<div class='Menu-Header'>Neu- Anmeldung  -  Hinweis</div>";
?>

Werter Kamerad, Werte Kameradin,
<?php
echo $anr . $name . ", ";
?>
<p>Die Daten sind abgespeichert. In Kürze bekommst Du eine E-Mail an die angegebene Adresse.
Bitte den dortigen Link
<span style='color:blue;'>
um die Anmeldung abzuschliessen</span>
anklicken, damit die Anmeldung nach der Adressprüfung abgeschlossen wird.</p>
<p>Danach kommt eine neue E-Mail mit der Bestätigung der Anmeldung. Danach ist die der interne Bereich der Home-Page nutzbar.</p>

<a href='<?php echo $e($basePath) ?>/FWHist'>Zurück zur Home Page</a>
<?php

if ($debug) {
    echo "<pre class=debug>MitglAnmeld_ph1_inc.php beendet</pre>";
}

?>
