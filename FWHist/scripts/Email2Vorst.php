<?php

declare(strict_types=1);

/**
 * E-Mail an den Vorstand (öffentliche Seite, ohne Login) - Nachfolger von VF_EM_Edit.php
 *
 * Empfänger: Mitglieder mit Vorstandsfunktion (mi_mitglieder.mi_vorst_funct <> ''), Adresse aus mi_email.
 * Die Adressen erscheinen nie im Formular. Schutz: Honigtopf, Mindestdauer, CSRF-Token, Nummern-Captcha
 * (große Ziffern für Sehschwache), Begrenzung je IP und je Absender-Adresse, Längen- und Zeilenumbruch-Prüfung.
 *
 * @author Josef Rohowsky - neu 2026
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Definition der Konstante für die Vorstands- Funktion des Mitgliedes im Verein
 *
 * @var M_Funktion
 */
const V_Funktion = array(
    " " => "keine Funktion",
    "O" => "Vereinsobmann",
    "P" => "1. Obmann Stellvertreter",
    "Q" => "2. Obmann Stellvertreter",
    "F" => "Finanzen",
    "G" => "Finanz Stellvertreter",
    "J" => "Jurist",
    "S" => "Schriftführer",
    "T" => "Schriftführer Stellvertreter"
);

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function () use ($systemBasePath) {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        file_put_contents(__DIR__ . '/Email2Vorst_fatal_error.log', $message, FILE_APPEND);
    }
});

// Öffentliche Seite: keine Fehlermeldungen an den Browser
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/Email2Vorst_php-error.log.txt');

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Mitglieder\API\VorstandRepository;
use FWHist\Services\HTMLPage;
use FWHist\Services\PublicFormGuard;
use FWHist\Services\RateLimiter;

$module = 'FWHist-EMail';
$sub_module = 'Ext';
$debug = false;

$_SESSION['BS_Prim'] ??= [];
$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/PHP_MailFuncsLib.php';

$envManager = new EnvSessionManager($systemBasePath);
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$DBD = new DB_GenericLog();
$pdo = $DBD->getPDO();

$pfg = new PublicFormGuard('email2vorst', $systemBasePath . 'login/AOrd_Verz/Logging/ratelimit/');
$vorstand = new VorstandRepository($pdo);
$groups = $vorstand->groups();            // Schlüssel => Funktion (Adressen bleiben auf dem Server)
$contact = (string) ($_SESSION['BS_Prim']['vema'] ?? '');   // Ausweich-Adresse für Besucher, die den Code nicht lesen können

$values = ['adresse' => '', 'betreff' => '', 'msgtext' => '', 'absender' => '', 'email' => ''];
$err = '';
$sent = false;
$isPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
$phase = $isPost ? (int) ($_POST['phase'] ?? 0) : 0;

if ($isPost) {
    foreach ($values as $name => $unused) {
        $values[$name] = PublicFormGuard::clean($_POST[$name] ?? '', $name === 'msgtext');
    }

    $onlyShow = $pfg->handleControls($_POST);   // "Neuer Code" / "Größere Ziffern": Eingaben behalten, nichts prüfen

    if (!$onlyShow && $phase === 1) {
        $err = (string) $pfg->validate($_POST);

        if ($err === '') {
            if (mb_strlen($values['betreff']) < 3 || mb_strlen($values['betreff']) > 80) {
                $err = 'Bitte geben Sie einen Betreff mit 3 bis 80 Zeichen ein.';
            } elseif (mb_strlen($values['msgtext']) < 10 || mb_strlen($values['msgtext']) > 3000) {
                $err = 'Bitte geben Sie eine Nachricht mit 10 bis 3000 Zeichen ein.';
            } elseif (mb_strlen($values['absender']) < 2 || mb_strlen($values['absender']) > 80) {
                $err = 'Bitte geben Sie Ihren Namen an (2 bis 80 Zeichen).';
            } elseif (mb_strlen($values['email']) > 120 || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
                $err = 'Bitte geben Sie eine gültige E-Mail-Adresse an, an die geantwortet werden kann.';
            } elseif (preg_match_all('~https?://|www\.~i', $values['msgtext']) > 2) {
                $err = 'Bitte nicht mehr als zwei Internet-Adressen in die Nachricht schreiben.';
            }
        }

        if ($err === '') {
            $client = RateLimiter::clientKey();
            $mailKey = hash('sha256', strtolower($values['email']));
            if (!$pfg->allow('em-ip-h', $client, 3, 3600) || !$pfg->allow('em-ip-d', $client, 10, 86400)
                || !$pfg->allow('em-mail-d', $mailKey, 3, 86400)) {
                $err = 'Sie haben in kurzer Zeit schon mehrere Nachrichten gesendet. Bitte versuchen Sie es später noch einmal.';
            }
        }

        if ($err === '') {
            $key = isset($groups[$values['adresse']]) ? $values['adresse'] : '';
            $to = $vorstand->addresses($key !== '' ? $key : null);
            $label = $key !== '' ? $groups[$key]['funct'] : 'Allgemein (alle Vorstandsmitglieder)';
            if ($to === [] && $contact !== '' && filter_var($contact, FILTER_VALIDATE_EMAIL) !== false) {
                $to = [$contact];   // keine Vorstands-Adresse hinterlegt: an die allgemeine Vereinsadresse
            }

            if ($to === []) {
                error_log('Email2Vorst: keine Empfänger-Adresse gefunden (mi_vorst_funct leer, vema nicht gesetzt)');
                $err = 'Die Nachricht kann zurzeit leider nicht zugestellt werden. Bitte versuchen Sie es später noch einmal.';
            } else {
                $e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $subject = '[Feuerwehrhistoriker] ' . $values['betreff'];
                $html = '<p><b>Nachricht über die Homepage (Formular „E-Mail an den Vorstand“)</b></p>'
                    . '<p>Von: ' . $e($values['absender']) . ' &lt;' . $e($values['email']) . '&gt;<br>An: ' . $e($label) . '</p><hr>'
                    . '<p>' . nl2br($e($values['msgtext'])) . '</p><hr>'
                    . '<p>Bitte direkt an die oben genannte Adresse antworten. Diese E-Mail wurde automatisch erzeugt.</p>';

                try {
                    sendEmail(implode(', ', $to), $subject, $html);
                    $sent = true;
                } catch (Throwable $ex) {
                    error_log('Email2Vorst: Versand fehlgeschlagen: ' . $ex->getMessage());
                    $err = 'Die Nachricht konnte nicht gesendet werden. Bitte versuchen Sie es später noch einmal.';
                }

                // Protokoll (Absender, Empfänger-Funktion, Text) - zur Nachverfolgung bei Missbrauch
                $logRec = "Mail- Log (Email2Vorst): " . date('Y-m-d H:i:s') . ' Orig.Requ: ' . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n"
                    . 'An: ' . $label . ' (' . count($to) . " Adresse(n))\nVon: " . $values['absender'] . ' <' . $values['email'] . ">\n"
                    . 'Betreff: ' . $values['betreff'] . "\n" . $values['msgtext'] . "\nErgebnis: " . ($sent ? 'gesendet' : 'FEHLER') . "\n\n";
                writelog($systemBasePath . 'login/AOrd_Verz/Logging/logs/Email2VorstLog', $logRec);

                if ($sent) {
                    $pfg->finish();
                }
            }
        }
    }
}

$pfg->show(!$isPost || $sent);   // Startzeit neu beim ersten Anzeigen; bei Fehlern bleibt sie erhalten

// ============================================================================================================
// Seite
// ============================================================================================================
$page = new HTMLPage('Nachricht an den Vorstand',
    $systemBasePath,
    $basePath,
    ['headExtras' => '',
        'type' => 'Form',
        'width' => '60em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

require 'Email2Vorst_ph0_inc.php';

$page->renderFooter();
?>
