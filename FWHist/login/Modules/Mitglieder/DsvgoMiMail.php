<?php

declare(strict_types=1);

/**
 * Info Mail an Mitglieder- Email DSVGO- Verständigung
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

# $_SESSION['BS_Prim'] ??= [];
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Auth\AccessGuard;
new EnvSessionManager();

use FWHist\Database\DB_GenericLog;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
#use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$module = 'MVW';
$sub_mod = 'all';

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MI_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});
    
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MI_Edit_php-error.log.txt');

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/PHP_MailFuncsLib.php';

$guard = AccessGuard::fromSession(new DB_GenericLog());
if (!$guard->isLoggedIn() || AccessGuard::isPublicView()) {
    header('Location: ' . $systemBasePath . '/');
    exit;
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');
$caller =  $envManager->getEnv('caller');

$header = "";

$page = new HTMLPage('Info über gespeicherte Daten, laut DSGVO',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
    ]
    );

$mailCSS = $basePath . "/FWHist/css/email_form.css";
$page->addCSS($mailCSS);
$page->loadConfig($configFileS);
$page->renderHeader();

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();
$pdo = $DBD->getPDO();

/** Daten des eingeloggten Mitgliedes / Benutzers erheben */

$abs = $_SESSION['BS_Prim']['BE']['uid'];

echo "<h1>Die Daten für <br>$abs<br> werden Aufgerufen </h1>";

$sql = "SELECT *
        FROM mi_mitglieder
        WHERE mi_email = :email";

$params = ['email' => $abs];

$row = $DBD->fetchOne($sql, $params);

echo "<h2>Die Daten werden aufbereitet </h2>"; 

$t_ID = $row['mi_id'];
$mtyp = $row['mi_mtyp'];
$m_o_typ = $row['mi_org_typ'];
$m_o_name = $row['mi_org_name'];
$t_H_F = $row['mi_anrede'];
$t_titel = $row['mi_titel'];
$t_dgr = $row['mi_dgr'];
$t_name = $row['mi_name'];
$t_vname = $row['mi_vname'];
$t_gebtag = $row['mi_gebtag'];
$t_email = $row['mi_email'];
$t_em_stat = $row['mi_email_status'];
$t_staat = $row['mi_staat'];
$t_plz = $row['mi_plz'];
$t_Ort = $row['mi_ort'];
$t_Adr = $row['mi_anschr'];
$t_Fax = $row['mi_fax'];
$t_TelNo = $row['mi_tel_handy'];
$t_vorst = $row['mi_vorst_funct'];
$t_ref_leit = $row['mi_ref_leit'];

$t_ref_in2 = $row['mi_ref_int_2'];
$t_ref_in3 = $row['mi_ref_int_3'];
$t_ref_in4 = $row['mi_ref_int_4'];
$t_sterbd = $row['mi_sterbdat'];
$t_beitritt = $row['mi_eintrdat'];
$t_austrdat = $row['mi_austrdat'];
$einvart = $row['mi_einv_art'];
$einverkl = $row['mi_einversterkl'];
$einv_dat = $row['mi_einv_dat'];
$f_einverkl = $einverkl;

if ($t_Adr == "") {
    $t_Adr = "kein Eintrag";
}
if ($t_vorst != "") {
    $vorst = "";
}
$RPmsg = "Adresse:<b> " . $row['mi_anschr'] . "</b><br>";

if ($row['mi_staat'] == "") {
    $Land = "kein Eintrag";
}
$ortstring = $row['mi_staat'];
$RPmsg .= "Land:<b> " . $row['mi_staat'] . "</b><br>";

if ($t_plz == "") {
    $t_plz = "kein Eintrag";
}
$ortstring = $ortstring . "-" . $row['mi_plz'];
$RPmsg .= "PLZ:<b> " . $row['mi_plz'] . "</b><br>";

if ($row['mi_ort'] == "") {
    $row['mi_ort'] = "kein Eintrag";
}
$text = "PLZ Ort";
$ortstring = $ortstring . " " . $row['mi_plz'];
$RPmsg .= "Ort:<b> " . $row['mi_ort'] . "</b><br>";

if ($t_TelNo == "") {
    $t_TelNo = "kein Eintrag";
}
$RPmsg .= "Telefonnummer:<b> " . $row['mi_tel_handy'] . "</b><br>";

if ($t_gebtag == "") {
    $t_gebtag = "kein Datum eingegeben";
}

$RPmsg .= "Geburtstag: <b>" . $row['mi_gebtag'] . "</b><br/> ";

$RPmsg .= "Eintrittsdatum: <b>" . $row['mi_eintrdat'] . "</b><br>";
$RPmsg .= "Mitglieds Beitr.: <b>" . $row['mi_m_beitr_bez'] . "</b><br>";
$RPmsg .= "Bezahlt bis :  <b>" . $row['mi_m_beitr_bez_bis'] . "</b><br>";
$RPmsg .= "Abo Beitr.: <b>" . $row['mi_m_abo_bez'] . "</b><br>";
$RPmsg .= "Abo Bez. bis: <b>" . $row['mi_m_abo_bez_bis'] . "</b><br>";

$RPmsg .= "";

if ($t_H_F == "Hr.") {
    $R_msg = "Lieber Herr ";
} else if ($t_H_F == "Fr.") {
    $R_msg .= "Liebe Frau ";
} else {
    $R_msg .= "Liebes Mitglied ";
}
if (! empty($t_titel)) {
    $R_msg .= $t_titel . " ";
}
$R_msg .= $t_name . " " . $t_vname . "!<br>\n";
$R_msg .= "Mit Bezug auf Anfrage von " . $t_name . " " . $t_vname . " finden Sie nachfolgend die Daten,<br> die in der Mitglieder-Datei des Vereines der Feuerwehrhistoriker in Niederösterreich gespeichert sind.<br>\n";
$R_msg .= "Sie haben Zugang zu den geschützten Seiten unserer IT-Plattform per Login mit Ihrem Benutzer-ID und Passwort,<br/> die Sie vom Administarator bekommen können (E-Mail: service @ feuerwehrhistoriker.at).<br><br>\n\n";
$R_msg .= "Ihre gespeicherte Mitgliedsnummer ist <b>" . $t_ID . "</b>, bitte verwenden Sie diese Nummer<br><b>immer als Referenz</b> bei Einzahlungen und Änderungsmeldungen.<br>\n";
$R_msg .= "Ihre gespeicherte Emailadresse ist <b>\"" . $t_email . "\"</b>.<br>\n<br>\n";


$R_msg .= "Nachfolgend Ihre persönlichen Daten, die in der Mitglieds-Datei abgespeichert sind:<br>";
$R_msg .= $RPmsg;

// Schlussformel an ALLE *************************
$R_msg .= "<p>Sie haben Ihre Zustimmung zur Datenspeicherung ";
if ($einverkl == "Y") {
    $R_msg .= "bereits am $einv_dat ";
    if ($einvart == "ONL") {
        $R_msg .= " Online ";
    } else {
        $R_msg .= " auf Papier ";
    }
} else {
    $R_msg .= " noch NICHT ";
}
$R_msg .= " gegeben.</p>";
$R_msg .= "<p>Falls die Aufzählung fehlerhaft erscheint oder Sie Änderungen wünschen, <br>schicken Sie bitte eine E-Mail an den Admin
   
.</p>
<p>Mit freundlichen Grüßen<br>Verein Feuerwehrhistoriker in Nö.</p>";
// " . $ini_arr['Config']['vema'] . "

$R_msg .= "Dies ist eine systemgenerierte Nachricht!<br>";
$diezeit = date("d. M Y \u\m  H:i:s");
$R_msg .= "Diese Information ist zusammengestellt und verschickt am $diezeit<br>";
// Ende Messagetext **********

$J_msg = "DSVGO Abfrage von Form_Name: $t_name $t_vname<br>";
$J_msg .= "Name: $t_name $t_vname<br>";
$J_msg .= "Mailversand: $diezeit</b><br>";

echo "<h2>Die Daten werden per E-Mail an $abs versendet </h2>"; 

sendEmail($t_email, // Empänger(Liste)
    "Gespeicherte Daten von " . $t_email, // Subject Text der EMail
    $R_msg, // Inhalt der Email in HTML format
    $reply_to = "service@feuerwehrhistoriker.at") ; //"$ini_arr['Config']['vema']); # 
    
    
$datum = date("d.m.Y:");
$zeit = date("H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'];
    $site = $_SERVER['REQUEST_URI'];
    $monate = array(
        1 => "Januar",
        2 => "Feber",
        3 => "März",
        4 => "April",
        5 => "Mai",
        6 => "Juni",
        7 => "Juli",
        8 => "August",
        9 => "September",
        10 => "Oktober",
        11 => "November",
        12 => "Dezember"
    );
    
    $monat = date("n");
    $jahr = date("y");
    $dateiname = "mi_dsvgo_auskunft";
    
    /* Get a the current date and time, formated */
    $shortdate = date("Y-m-d H:i:s");
    
    /* Build log string for writing */
    // $log_rec = "\nReferer: " . $HTTP_REFERER . "\n";
    # $log_rec = "\nIP: " . $_SERVER['REMOTE_ADDR'] . ", Hostname: " . $_SERVER['REMOTE_HOST'] . "\n";
    $log_rec = "With: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
    # $log_rec .= "requested von REMOTE_USER:>>>>" . $_SERVER['REMOTE_USER'] . "<<<<<\n";
    $log_rec .= "Page: " . $_SERVER['REQUEST_URI'] . "\n";
    $log_rec .= "Requester-Name: " . $t_name . "\n";
    $log_rec .= "Requester-Email: " . $t_email . "\n";
  #  $log_rec .= "Error Kodes: " . "Noerr = " . $Noerr . " | .. errM = " . $errM . " | ..  emerr = " . $emerr . " | .. err3 = " . $err3 . "| ..err2 = " . $err2 . "| ..err1 = " . $err1 . " | Ende Errkode\n";
    $log_rec .= "MITGLIEDSNUMMER ( Datensatz-Nr.): " . $t_ID . "\n";
    $log_rec .= "Anrede: " . $t_H_F . "\n";
    $log_rec .= "Titel: " . $t_titel . "\n";
    $log_rec .= "Name: " . $t_name . $t_vname . "\n";
    $log_rec .= "Email: " . $t_email . "\n";
    
    $log_rec .= "Einverstaendnis Datenschutz: $einvart $einverkl  $einv_dat \n";
    
    $eintragen = "Datum - - [$datum$zeit] $log_rec ";
    $adminmail = $eintragen . "****** Adminmail Ende ******";
    
    
    writelog($dateiname, // DSN des MonatsLogFiles (wird mit Jahr Monat und .txt ergänzt)
        $log_rec // der Text welcher Einzutragen ist (wird mit Systemdaten ergänzt)
        );
    
    sendEmail("service@feuerwehrhistoriker.at, josef@kexi.at", // Empänger(Liste)
        "Yellow.log-Adminkopie von " . $t_email, // Subject Text der EMail
        $R_msg . $J_msg, // Inhalt der Email in HTML format
        $reply_to = "service@feuerwehrhistoriker.at");

    echo "Die Daten wurden per E-Mail abgeschickt.<br>";
    echo "Das Formularfenster wird geschlossen; das Menü bleibt geöffnet.";

    echo "<script>
    setTimeout(function() {
        if (window.opener && !window.opener.closed) {
            window.opener.focus();
            window.close();
        } else {
            window.history.back();
        }
    }, 2000); // 2 Sekunden warten
</script>";
    exit;

$page->renderFooter();
    
?>