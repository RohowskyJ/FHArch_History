<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Anmeldung eines neuen Mitgliedes (öffentliche Seite, ohne Login)
 *
 * @author Josef Rohowsky - neu 2018, überarbeitet 2026
 *
 * Schutz: Honigtopf, Mindestdauer, CSRF-Token, Nummern-Captcha (große Ziffern für Sehschwache), Begrenzung je IP und
 * je E-Mail-Adresse. Aus dem Formular werden nur die vorgesehenen Felder übernommen; Status, Token, Zahlungsfelder und
 * Einverständnis setzt der Server. Die Bestätigungs-E-Mail mit dem Link geht an den Anmelder.
 */

$RoBase = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

// Composer Autoload
require_once $RoBase . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;
use FWHist\Services\PublicFormGuard;
use FWHist\Services\RateLimiter;
use FWHist\Services\TableColumnMetadata;

$_SESSION['BS_Prim'] ??= [];

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        file_put_contents(__DIR__ . '/INDEX_public_fatal_error.log', $message, FILE_APPEND);
    }
});

// Öffentliche Seite: keine Fehlermeldungen an den Browser
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/Anmeld_php-error.log.txt');

$module = "FWHist-Anmeld";
$sub_module = 'Ext';

$debug = false ;

$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

require $RoBase . 'login/Services/FuncsLib.php';
require $RoBase . 'login/Services/CommFuncsLib.php';
require $RoBase . 'login/Services/ConstLib.php';
require $RoBase . 'login/Services/PHP_MailFuncsLib.php';

$envManager = new EnvSessionManager($RoBase);
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');
$systemBasePath = $envManager->getEnv('systemBasePath') ?: $RoBase;

$miBeitr = ''; // Mitgliedsbeitrag, wird aus config_s_?.ini gesetzt (Anzeige entfällt, solange leer)

// ============================================================================================================
// Datenbank
// ============================================================================================================
$DBD = new DB_GenericLog();
$pdo = $DBD->getPDO();

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
$columnsByTables = $meta->getColumnsForTables(['mi_anmeldung']);
$mitgl = new MI_MitgliederModule($DBD);

$pfg = new PublicFormGuard('mitglanmeld', $RoBase . 'login/AOrd_Verz/Logging/ratelimit/');
$contact = (string) ($_SESSION['BS_Prim']['vema'] ?? '');   // Ausweich-Adresse für Besucher, die den Code nicht lesen können

// ============================================================================================================
// Felder des Formulars: Name => [maximale Länge, Pflichtfeld]. Nur diese Felder werden übernommen.
// ============================================================================================================
$fieldSpec = [
    'mi_anrede' => [3, true], 'mi_titel' => [10, false], 'mi_name' => [50, true], 'mi_vname' => [50, true],
    'mi_n_titel' => [10, false], 'mi_dgr' => [10, false], 'mi_gebtag' => [10, true], 'mi_staat' => [10, false],
    'mi_anschr' => [50, true], 'mi_plz' => [7, true], 'mi_ort' => [50, true], 'mi_tel_handy' => [100, true],
    'mi_fax' => [50, false], 'mi_email' => [100, true], 'mi_mtyp' => [5, false], 'mi_org_typ' => [20, false],
    'mi_org_name' => [50, false],
];

$neu = array_fill_keys(array_keys($fieldSpec), '');
$neu['mi_mtyp'] = 'UM';
$neu['mi_anrede'] = 'Hr.';
$neu['mi_ref_int_2'] = $neu['mi_ref_int_3'] = $neu['mi_ref_int_4'] = '';

$phase = 0;       // 0 = Formular anzeigen, 1 = angemeldet (Bestätigungsseite)
$err = $mail_err = "";
$Err_msg = [];

$isPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
if ($isPost) {
    foreach ($fieldSpec as $name => $spec) {
        $neu[$name] = PublicFormGuard::clean($_POST[$name] ?? '');
    }
    foreach (['mi_ref_int_2', 'mi_ref_int_3', 'mi_ref_int_4'] as $name) {
        $neu[$name] = (($_POST[$name] ?? '') === 'Y') ? 'Y' : '';
    }
    $staatId = strtoupper(PublicFormGuard::clean($_POST['staat_id'] ?? ''));   // Hidden-Feld der Staaten-Auswahl

    $onlyShow = $pfg->handleControls($_POST);   // "Neuer Code" / "Größere Ziffern": Eingaben behalten, nichts prüfen

    if (!$onlyShow && (int) ($_POST['phase'] ?? 0) === 1) {
        $err = (string) $pfg->validate($_POST);

        if ($err === '') {
            // ---- Eingaben prüfen -------------------------------------------------------------------------
            foreach ($fieldSpec as $name => [$max, $required]) {
                if ($required && $neu[$name] === '') {
                    $Err_msg[$name] = 'Dieses Feld ist erforderlich.';
                } elseif (mb_strlen($neu[$name]) > $max) {
                    $Err_msg[$name] = "Maximale Länge von {$max} Zeichen überschritten.";
                }
            }
            if (!isset($Err_msg['mi_anrede']) && !in_array($neu['mi_anrede'], ['Fr.', 'Hr.'], true)) {
                $Err_msg['mi_anrede'] = 'Bitte Frau oder Herr wählen.';
            }
            if (!isset($Err_msg['mi_mtyp'])) {
                if ($neu['mi_mtyp'] === '') {
                    $neu['mi_mtyp'] = 'UM';
                } elseif (!array_key_exists($neu['mi_mtyp'], M_Typ)) {
                    $Err_msg['mi_mtyp'] = 'Ungültige Auswahl.';
                }
            }
            if ($neu['mi_org_typ'] !== '' && !array_key_exists($neu['mi_org_typ'], M_Org)) {
                $Err_msg['mi_org_typ'] = 'Ungültige Auswahl.';
            } elseif ($neu['mi_org_typ'] !== 'Privat' && $neu['mi_org_typ'] !== '' && $neu['mi_org_name'] === '') {
                $Err_msg['mi_org_name'] = 'Bitte den Namen der Organisation eingeben.';
            }
            if ($neu['mi_staat'] !== '' && preg_match('/^[A-Za-z]{2,3}$/', $neu['mi_staat']) !== 1) {
                $Err_msg['mi_staat'] = 'Bitte das Länderkürzel (z. B. AT) angeben.';
            }

            // Geburtsdatum: Jahr-Monat-Tag oder Tag.Monat.Jahr, plausibel (nach 1900, mindestens 10 Jahre alt)
            $gebtagSql = null;
            if (!isset($Err_msg['mi_gebtag'])) {
                foreach (['!Y-m-d', '!d.m.Y'] as $format) {
                    $d = DateTimeImmutable::createFromFormat($format, $neu['mi_gebtag']);
                    $warn = DateTimeImmutable::getLastErrors();
                    if ($d !== false && ($warn === false || ($warn['warning_count'] + $warn['error_count']) === 0)) {
                        $gebtagSql = $d->format('Y-m-d');
                        break;
                    }
                }
                if ($gebtagSql === null || $gebtagSql < '1900-01-01' || $gebtagSql > date('Y-m-d', strtotime('-10 years'))) {
                    $Err_msg['mi_gebtag'] = 'Bitte ein gültiges Geburtsdatum eingeben.';
                    $gebtagSql = null;
                }
            }

            if (!isset($Err_msg['mi_email'])) {
                if (filter_var($neu['mi_email'], FILTER_VALIDATE_EMAIL) === false) {
                    $mail_err = "Ungültiges E-Mail-Format";
                    $Err_msg['mi_email'] = 'Bitte eine gültige E-Mail-Adresse eingeben.';
                } elseif ($mitgl->emailExists($neu['mi_email'])) {
                    $mail_err = "E-Mail Adresse bereits vorhanden";
                    $Err_msg['mi_email'] = 'Diese E-Mail-Adresse ist bereits vorhanden.';
                }
            }

            if (($_POST['einverkl'] ?? '') !== 'Y') {
                $Err_msg['einverkl'] = 'Die Einverständniserklärung wird laut DSGVO zwingend benötigt.';
            }

            if ($Err_msg !== []) {
                $err = 'Bitte die markierten Angaben prüfen.';
            }
        }

        if ($err === '') {
            // ---- Begrenzung je IP und je Adresse (zählt nur echte Anmeldungen) ---------------------------------
            $client = RateLimiter::clientKey();
            if (!$pfg->allow('anm-ip-h', $client, 5, 3600) || !$pfg->allow('anm-ip-d', $client, 15, 86400)
                || !$pfg->allow('anm-mail-d', hash('sha256', strtolower($neu['mi_email'])), 2, 86400)) {
                $err = 'Es wurden in kurzer Zeit schon mehrere Anmeldungen gesendet. Bitte versuchen Sie es später noch einmal.';
            }
        }

        if ($err === '') {
            // ---- Datensatz zusammenstellen: Formularfelder + vom Server gesetzte Werte -------------------------
            $now = date('Y-m-d H:i:s');
            $rec = [];
            foreach ($fieldSpec as $name => $spec) {
                $rec[$name] = $neu[$name];
            }
            $rec['mi_ref_int_2'] = $neu['mi_ref_int_2'];
            $rec['mi_ref_int_3'] = $neu['mi_ref_int_3'];
            $rec['mi_ref_int_4'] = $neu['mi_ref_int_4'];
            $rec['mi_gebtag'] = $gebtagSql;
            if (preg_match('/^[A-Z]{2,3}$/', $staatId) === 1) {
                $rec['mi_staat'] = $staatId;
            }
            if ($rec['mi_mtyp'] === 'UM' && $rec['mi_org_name'] !== '') {
                $rec['mi_mtyp'] = 'FG';
            }
            $rec += [
                'mi_id' => 0,
                'mi_neu_chkd' => 'N',
                'mi_eintrdat' => date('Y-m-d'),
                'mi_sterbdat' => null, 'mi_austrdat' => null, 'mi_ref_leit' => null, 'mi_email_status' => null,
                'mi_m_beitr_bez' => null, 'mi_m_abo_bez' => null, 'mi_m_beitr_bez_bis' => null,
                'mi_m_abo_bez_bis' => null, 'mi_abo_ausg' => null,
                'mi_einversterkl' => 'Y', 'mi_einv_art' => 'ONL', 'mi_einv_dat' => date('Y-m-d'),
                'mi_neu_token' => bin2hex(random_bytes(16)),   // Token für den E-Mail-Check
                'mi_neu_tok_erz' => $now,
                'mi_changed_id' => 999999,                      // Kennung "anonyme Anmeldung"
                'mi_changed_at' => $now,
            ];

            try {
                $rec['mi_neu_nr'] = $mitgl->createMiAnmeldg($rec);
            } catch (Throwable $ex) {
                error_log('MitglAnmeld: Speichern fehlgeschlagen: ' . $ex->getMessage());
                $err = 'Die Anmeldung konnte leider nicht gespeichert werden. Bitte versuchen Sie es später noch einmal.';
            }

            if ($err === '') {
                $neu = $rec;
                $pfg->finish();
                $phase = 1;
            }
        }
    }
}

$pfg->show(!$isPost || $phase === 1);   // Startzeit neu beim ersten Anzeigen; bei Fehlern bleibt sie erhalten

// ============================================================================================================
// Seite
// ============================================================================================================
$page = new HTMLPage('Mitgliedsanmeldung',
    $systemBasePath,
    $basePath,
    ['headExtras' => '',
        'type' => 'Form',
        'width' => '60em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

switch ($phase) {
    case 0:
        require 'MitglAnmeld_ph0_inc.php';
        break;
    case 1:
        require 'MitglAnmeld_ph1_inc.php';
        break;
}
$page->renderFooter();
?>
