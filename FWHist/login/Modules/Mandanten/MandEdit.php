<?php

declare(strict_types=1);

/**
 * Mandanten- Daten- Wartung (ma_mandanten)
 *
 * Zugriff nur mit ADM-MA (oder ADM-ALLE): Die Stammdaten enthalten Namen, Adressen, Telefon, E-Mail und die
 * Einverständniserklärungen der Mandanten. Gespeichert werden nur die Felder des Formulars (Allowlist),
 * mit CSRF-Token; Bearbeiter und Zeitstempel setzt der Server.
 *
 * @author Josef Rohowsky - neu 2026
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfen, ob Session gültig ist
if (empty($_SESSION['BS_Prim'])) {
    // Session ungültig → Weiterleitung auf externe URL
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        header('Location: /localhost/Feuerwehr_Archiv/FWHist/');
        exit;
    }
    header('Location: https://www.feuerwehrhistoriker.at/FWHist/');
    exit;
}

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../../login/AOrd_Verz/Logging/Mand_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

// Im Betrieb keine Fehlermeldungen an den Browser, nur ins Log
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../../login/AOrd_Verz/Logging/Mand_Edit_php-error.log.txt');

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Auth\AccessGuard;
use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Mandanten\API\DB_Mandanten;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex_Ext;
use FWHist\Services\HTMLPage;

$module = 'ADM-MAND';
$sub_mod = 'Edit';

$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

// ============================================================================================================
// RBAC - VOR jeder Ausgabe
// ============================================================================================================
$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canEditMandantStamm()) {
    AccessGuard::deny('Für die Mandanten-Stammdaten ist die Berechtigung ADM-MA erforderlich.');
}
$canEdit = true;   // Seite ist nur für ADM-MA/ADM-ALLE erreichbar

$phase = (int) ($_POST['phase'] ?? 0);
if ($phase === 1) {
    AccessGuard::requireCsrf();   // Token aus dem Formular (MandEdit_ph0_inc.php)
}

$ei_id = (int) ($_POST['ei_id'] ?? $_GET['ID'] ?? 0);   // 0 = neuer Mandant

$pdo = $DBD->getPDO();
$mand = new DB_Mandanten($DBD);

$current = null;
if ($ei_id > 0) {
    $current = $mand->getMandById($ei_id);
    if ($current === null) {
        AccessGuard::deny('Mandant nicht gefunden.', 404);
    }
}

// ============================================================================================================
// Phase 1: Speichern (Rechte und CSRF sind oben geprüft)
// ============================================================================================================
if ($phase === 1) {
    // Nur die Felder des Formulars; Schlüssel (ei_id), Bearbeiter und Zeitstempel werden nie aus dem Request übernommen
    $formFields = [
        'ei_mitglnr', 'ei_org_typ', 'ei_org_name', 'ei_staat', 'ei_bdld', 'ei_bezirk', 'ei_fwkz', 'ei_grdgj',
        'ei_adresse', 'ei_plz', 'ei_ort', 'ei_tel', 'ei_fax', 'ei_handy', 'ei_email', 'ei_internet',
        'ei_abgdat', 'ei_neueigner', 'ei_urh_kurzz', 'ei_media',
        'ei_kont_name', 'ei_titel', 'ei_vname', 'ei_name', 'ei_dgr',
        'ei_wlpriv', 'ei_vopriv', 'ei_wlmus', 'ei_vomus', 'ei_wlinv', 'ei_voinv', 'ei_voinf', 'ei_vofo', 'ei_voar',
        'ei_drwvs', 'ei_drneu',
    ];
    // Nur Spalten, die es in der Tabelle gibt (ein Formularfeld ohne Spalte darf das Speichern nicht verhindern)
    $columns = array_keys($current ?? ($mand->select('ma_mandanten', ['*'], [], [], 1)[0] ?? []));

    $neu = [];
    foreach ($formFields as $field) {
        if ($columns !== [] && !in_array($field, $columns, true)) {
            continue;
        }
        $value = $_POST[$field] ?? null;
        if (is_string($value)) {
            $neu[$field] = trim($value);
        }
    }

    // Staat und Bundesland kommen aus den Hidden-Feldern der Autocomplete-Auswahl
    foreach (['staat_id' => 'ei_staat', 'bdld_id' => 'ei_bdld'] as $postKey => $column) {
        $value = $_POST[$postKey] ?? '';
        if (is_string($value) && trim($value) !== '') {
            $neu[$column] = trim($value);
        }
    }

    date_default_timezone_set('Europe/Berlin');
    $neu['ei_changed_id'] = $guard->userId();
    $neu['ei_changed_at'] = date('Y-m-d H:i:s');

    if ($ei_id === 0) { # Neueingabe
        $ret = $mand->insertMand($neu);
    } else { # Update
        $ret = $mand->updateMand($neu, ['ei_id' => $ei_id]);
    }

    header('Location: MandantenList.php');
    exit;
}

// ============================================================================================================
// Seite
// ============================================================================================================
$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('Mandanten- Verwaltung',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
        'jq' => false,
        'jqui' => false,
        'jq_accordion' => false,
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
$columnsByTables = $meta->getColumnsForTables(['ma_mandanten' ]);

if ($current === null) {
    $neu = [
        'ei_id' => 0,
        'ei_mitglnr' => 0,
        'ei_staat' => "AT",
        "ei_bd_abk" => "NOE",
        'ei_bdld' => "",
        'ei_bezirk' => "",
        'ei_organi' => "",
        'ei_org_typ' => "",
        'ei_org_name' => "",
        "ei_kont_name" => "",
        'ei_fwkz' => "",
        'ei_grdgj' => "",
        'ei_titel' => "",
        'ei_vname' => "",
        'ei_name' => "",
        'ei_n_titel' => "",
        'ei_dgr' => "",
        'ei_adresse' => "",
        'ei_plz' => "",
        'ei_ort' => "",
        'ei_tel' => "",
        'ei_fax' => "",
        'ei_handy' => "",
        'ei_email' => "",
        'ei_internet' => "",
        'ei_url_chkd' => "",
        'ei_url_obsolete' => "",
        'ei_sterbdat' => '',
        'ei_abgdat' => '',
        'ei_urh_kurzz' => "",
        "ei_media" => "",
        "ei_neueigner" => "",
        "ei_wlpriv" => "",
        "ei_vopriv" => "",
        "ei_wlmus" => "",
        "ei_vomus" => "",
        "ei_wlinv" => "",
        "ei_voinv" => "",
        "ei_voinf" => "",
        "ei_vofo" => "",
        "ei_voar" => "",
        "ei_drwvs" => "",
        "ei_drneu" => "",
        "ei_new_id" => 0,
        "ei_new_at" => "",
        'ei_changed_id' => 0 ,
        'ei_changed_at' => ""
    ];
} else {
    $neu = $current;
    $neu['staat_id'] = '';
    $neu['staat'] = ""; //Auslesen!
}

$editProtect = !$canEdit;
require 'MandEdit_ph0_inc.php';

$page->renderFooter();
?>
