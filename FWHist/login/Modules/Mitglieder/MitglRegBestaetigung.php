<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Mitgliederverwaltung - Bestätigung der Registrierung per E-Mail-Token
 *
 * Zuständigkeit (bewusst schlank gehalten):
 *   - Token prüfen (ungültig / abgelaufen / bereits bestätigt)
 *   - mi_anmeldung.mi_neu_chkd = 'J' setzen, Token löschen
 *   - Bestätigungsmail an den Registrierenden
 *   - Benachrichtigungsmail an den Mitglieder-Admin (service@feuerwehrhistoriker.at)
 *
 * Das eigentliche Anlegen von mi_mitglieder / fv_benutzer / fv_ben_dat / fv_erlauben /
 * fv_rolle passiert NICHT mehr hier, sondern erst in MitglAktivierung.php, nachdem
 * ein Admin die Anmeldung gesichtet und freigegeben hat.
 *
 * @author Josef Rohowsky - neu 2026 / restrukturiert 2026-09-25
 */

$RoBase = $_SESSION['BS_Prim']['RoBase'] ?? '';
require_once $RoBase . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\HTMLPage;

$_SESSION['BS_Prim'] ??= [];

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        file_put_contents(__DIR__ . '/../../../login/AOrd_Verz/Logging/MitglReg_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../../login/AOrd_Verz/Logging/MitglReg_php-error.log.txt');

$envManager     = new EnvSessionManager($RoBase);
$basePath       = $envManager->getEnv('basePath');
$configFileS    = $envManager->getEnv('configFileS');
$systemBasePath = $envManager->getEnv('systemBasePath');

require $RoBase . 'login/Services/FuncsLib.php';
require $RoBase . 'login/Services/CommFuncsLib.php';
require $RoBase . 'login/Services/ConstLib.php';
require $RoBase . 'login/Services/PHP_MailFuncsLib.php';

// -------------------- Konfiguration --------------------
const TOKEN_MAX_AGE_HOURS = 48;

$page = new HTMLPage(
    'Mitglieds- Registrierung',
    $systemBasePath,
    $basePath,
    [
        'headExtras'    => '',
        'type'          => 'Form',
        'width'         => '60em',
    ]
);
$page->loadConfig($configFileS);
$page->renderHeader();

initial_debug('POST', 'GET');

$DBD = new DB_GenericLog();
$pdo = $DBD->getPDO();

// -------------------- Input --------------------
$token = $_GET['token'] ?? '';
$token = is_string($token) ? trim($token) : '';

$unguelt = ($token === '' || strlen($token) > 128);
$neu = null;

if (!$unguelt) {
    $neu = $DBD->fetchOne(
        "SELECT mi_neu_nr, mi_neu_chkd, mi_neu_token, mi_neu_tok_erz,
                mi_anrede, mi_titel, mi_vname, mi_name, mi_n_titel, mi_email
         FROM mi_anmeldung
         WHERE mi_neu_token = :token
         LIMIT 1",
        [':token' => $token]
    );

    if ($neu === null) {
        $unguelt = true;
    }
}

// -------------------- Sofortiger Abbruch: ungültiger Token --------------------
if ($unguelt) {
    echo "<p>Ungültiger Bestätigungscode - Abbruch. Bitte Anmeldung neu ausfüllen.</p>";
    $page->renderTrailer();
    exit;
}

$bestaetigt = (($neu['mi_neu_chkd'] ?? 'N') === 'J');

// -------------------- Sofortiger Abbruch: bereits bestätigt --------------------
// Kein erneuter Mailversand/Logeintrag bei wiederholtem Öffnen des Links
// (z.B. durch Mail-Sicherheitsscanner, Doppelklick, erneuter Aufruf).
if ($bestaetigt) {
    echo "<p>Diese Anmeldung wurde bereits bestätigt und wird von unserem Mitglieder-Team geprüft.</p>";
    $page->renderTrailer();
    exit;
}

$abgelaufen = false;
if (!empty($neu['mi_neu_tok_erz'])) {
    $createdAt  = new DateTime((string)$neu['mi_neu_tok_erz']);
    $now        = new DateTime('now');
    $diffHours  = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;
    $abgelaufen = $diffHours > TOKEN_MAX_AGE_HOURS;
}

// -------------------- Sofortiger Abbruch: Token abgelaufen --------------------
if ($abgelaufen) {
    echo "<p>Der Bestätigungslink ist abgelaufen (mehr als " . TOKEN_MAX_AGE_HOURS . " Stunden). Bitte die Anmeldung neu ausfüllen.</p>";
    $page->renderTrailer();
    exit;
}

// -------------------- Normaler Ablauf: Anmeldung als bestätigt markieren --------------------
try {
    $DBD->beginTransaction();

    // Mit Sperre erneut lesen -> schützt vor Race Conditions bei parallelen Aufrufen
    $stLock = $pdo->prepare("SELECT mi_neu_chkd FROM mi_anmeldung WHERE mi_neu_nr = :nr FOR UPDATE");
    $stLock->execute([':nr' => $neu['mi_neu_nr']]);
    $rowLock = $stLock->fetch();

    if ($rowLock === false || ($rowLock['mi_neu_chkd'] ?? 'N') === 'J') {
        // Zwischenzeitlich bereits bestätigt (z.B. zwei parallele Requests) - kein Fehler
        $DBD->rollBack();
        echo "<p>Diese Anmeldung wurde soeben bereits bestätigt.</p>";
        $page->renderTrailer();
        exit;
    }

    $DBD->update(
        'mi_anmeldung',
        ['mi_neu_chkd' => 'J', 'mi_neu_token' => null],
        ['mi_neu_nr' => $neu['mi_neu_nr']],
        '',
        false
    );

    $DBD->commit();
} catch (\Throwable $e) {
    $DBD->rollBack();
    error_log('MitglRegBestaetigung: Fehler beim Bestätigen (mi_neu_nr=' . $neu['mi_neu_nr'] . '): ' . $e->getMessage());
    echo "<p>Es ist ein technischer Fehler aufgetreten. Bitte später erneut versuchen oder das Mitglieder-Team kontaktieren.</p>";
    $page->renderTrailer();
    exit;
}

$adr_list = (new \FWHist\AllgVerw\API\AdminMailAddressRepository($pdo))
->getActiveAddressesByGroup('Mitgl');
echo __LINE__ . " $adr_list , josef@kexi.at  ,  VFHNÖ Mitglieds- Neuanmeldung 1. Teil , $text <br>";

/** falls keine Zusatzmail definiert wurde, an die Default Mail- Adresse schicken */
if ($adr_list == '') {
    // Mail an den ANtragssteller
    $ini_arr = parse_ini_file($basePath . 'login/common/config_s.ini',True,INI_SCANNER_NORMAL);
    $adr_list = $ini_arr['Config']['vema'];
}

// -------------------- Bestätigungsmail an Registrierenden --------------------
$anr = (($neu['mi_anrede'] ?? '') === 'Hr.') ? 'Werter Kamerad, Herr ' : 'Werte Kameradin, Frau ';
$anzeigeName = trim(
    ($neu['mi_titel'] ?? '') . ' ' . ($neu['mi_vname'] ?? '') . ' ' .
    ($neu['mi_name']  ?? '') . ' ' . ($neu['mi_n_titel'] ?? '')
);

$subject = "Anmeldung bestätigt - wird geprüft";
$text = $anr . $anzeigeName . ", <h2>Danke für die Bestätigung!</h2>"
      . "<p>Ihre E-Mail-Adresse wurde bestätigt. Die Anmeldung wird nun von unserem Mitglieder-Team geprüft "
      . "und in Kürze freigeschaltet. Sie erhalten danach eine weitere E-Mail mit den Zugangsdaten.</p>"
      . "<p>Mit Kameradschaftlichen Grüßen, der Mitglieder- Verantwortliche.</p>";

sendEmail($neu['mi_email'], $subject, $text);

// -------------------- Benachrichtigung an Mitglieder-Admin --------------------
$adminUrl = rtrim($basePath, '/') . "/MitglAktivierung.php?nr=" . urlencode((string)$neu['mi_neu_nr']);

$adminSubject = "Neue Mitgliedsanmeldung wartet auf Aktivierung";
$adminText = "<p>Eine neue Mitgliedsanmeldung wurde per E-Mail bestätigt und wartet auf Prüfung/Aktivierung.</p>"
           . "<p>Name: "   . htmlspecialchars($anzeigeName, ENT_QUOTES) . "<br>"
           . "E-Mail: "    . htmlspecialchars((string)$neu['mi_email'], ENT_QUOTES) . "<br>"
           . "Anmeldung-Nr.: " . (int)$neu['mi_neu_nr'] . "</p>"
           . "<p><a href=\"" . htmlspecialchars($adminUrl, ENT_QUOTES) . "\">Zur Aktivierungs-Liste öffnen</a></p>";

sendEmail($adr_list, $adminSubject, $adminText);

// -------------------- Log --------------------
$datum = date("d.m.Y:");
$zeit  = date("H:i:s");
$dsn   = "AnmeldLog";

$log_rec  = "E-Mail-Bestätigung eingegangen $datum $zeit\n";
$log_rec .= "Anmeldung-Nr.: " . $neu['mi_neu_nr'] . "\n";
$log_rec .= "Name: " . $anzeigeName . "\n";
$log_rec .= "E-Mail: " . $neu['mi_email'] . "\n";
$log_rec .= "Orig.TCP = " . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n";

writelog($dsn, $log_rec);

echo "<p>Danke! Ihre E-Mail-Adresse wurde bestätigt. Die Anmeldung wird nun geprüft und in Kürze freigeschaltet.</p>";
$page->renderTrailer();
