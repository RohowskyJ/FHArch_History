<?php

/**
 * Mandanten-Erlaubnis für Benutzer zuordnen: Verwaltung, Wartung (Tabelle fv_benutzer_mandant)
 *
 * Zugriff nur mit ADM-ZUGR (oder ADM-ALLE). Die Erlaubnis gilt für ONU-ALL: Schreiben in den Daten des Mandanten
 * (Archiv, Inventar, Medien, Fahrzeuge/Geräte). Fachrollen (ADM-ARC ...) gelten ohnehin für alle Mandanten.
 *
 * @author Josef Rohowsky - neu 2020
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
register_shutdown_function(function () use ($systemBasePath) {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents($systemBasePath . 'login/AOrd_Verz/MandErlEdit_fatal_error.log', $message, FILE_APPEND);
    }
});

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Auth\AccessGuard;
use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_Benutzer;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Services\HTMLPage;

$module = 'ADM-MandErl';
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
if (!$guard->canManageAccess()) {
    AccessGuard::deny('Für die Mandanten-Erlaubnis ist die Berechtigung ADM-ZUGR erforderlich.');
}

$phase = (int) ($_POST['phase'] ?? 0);
if ($phase === 1) {
    AccessGuard::requireCsrf();   // Token aus dem Formular (MandErlEdit_ph0_inc.php)
}

$pdo = $DBD->getPDO();
$benu = new DB_Benutzer(DB_GenericLog::getInstance());

// Eingaben: Zuordnung (fm_id) und Benutzer (be_id) - beides wird gegen die Datenbank geprüft
$fm_id = (int) ($_POST['fm_id'] ?? $_GET['ID'] ?? 0);
$be_id = isset($_GET['beId']) ? (int) $_GET['beId'] : (int) ($_POST['be_id'] ?? 0);

if ($be_id < 1 || !$benu->userExists($be_id)) {
    AccessGuard::deny('Benutzer nicht gefunden.', 404);
}

// Bestehende Zuordnung: muss zu diesem Benutzer gehören
$existing = null;
if ($fm_id > 0) {
    $existing = $benu->getMandantPermById($fm_id, $be_id);
    if ($existing === null) {
        AccessGuard::deny('Mandanten-Erlaubnis nicht gefunden.', 404);
    }
}

$mandanten = $benu->getMandantenAll();   // [ei_id => Name]

date_default_timezone_set('Europe/Berlin');

if ($phase === 1) {
    // Nur diese Werte werden aus dem Formular übernommen; Benutzer, Zeitstempel und Bearbeiter setzt der Server
    $eiId = (int) ($_POST['descript'] ?? 0);
    if ($eiId < 1) {
        $eiId = (int) ($_POST['ei_id'] ?? 0);
    }
    if (!isset($mandanten[$eiId])) {
        AccessGuard::deny('Mandant nicht gefunden.', 400);
    }
    $aktiv = (string) ($_POST['fm_aktiv'] ?? 'a');
    if (!in_array($aktiv, ['a', 'i'], true)) {
        AccessGuard::deny('Ungültiger Status.', 400);
    }
    if ($benu->mandantPermExists($be_id, $eiId, $fm_id)) {
        AccessGuard::deny('Dieser Mandant ist dem Benutzer bereits zugeordnet.', 409);
    }

    $now = date('Y-m-d H:i:s');
    $by = $guard->userId();
    $neu = ['ei_id' => $eiId, 'fm_aktiv' => $aktiv, 'fm_changed_id' => $by, 'fm_changed_at' => $now];
    if ($existing === null) {
        $neu += ['be_id' => $be_id, 'fm_created_id' => $by, 'fm_created_at' => $now];
    }
}

$profileId = $benu->getProfileIdByBeId($be_id);   // Schlüssel von BenEdit.php
$benName = $benu->getUserNameByBeId($be_id);

// ============================================================================================================
// Seite
// ============================================================================================================
echo "<!doctype html>";

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('Benutzer- Verwaltung',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$columnsByTables = $meta->getColumnsForTables(['fv_benutzer_mandant', 'ma_mandanten']);

// ============================================================================================================
// Phase 0: Anzeige
// ============================================================================================================
if ($phase === 0) {
    if ($existing === null) {
        $neu['fm_id'] = 0;
        $neu['be_id'] = $be_id;
        $neu['ei_id'] = $neu['fm_aktiv'] = "";
        $neu['fm_created_id'] = $neu['fm_changed_id'] = 0;
        $neu['fm_created_at'] = $neu['fm_changed_at'] = "0000-00-00 00:00:00";
    } else {
        $neu = $existing;
    }
    $neu['descript'] = "";
    $neu['Mandant'] = "";

    if ($debug) {
        echo '<pre class=debug>';
        echo '<hr>$neu: ';
        var_dump($neu);
        echo '</pre>';
    }
}

switch ($phase) {
    case 0:
        require 'MandErlEdit_ph0_inc.php';
        break;
    case 1:
        require "MandErlEdit_ph1_inc.php";
        break;
}
$page->renderFooter();
?>
