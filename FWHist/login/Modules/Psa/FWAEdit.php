<?php

declare(strict_types=1);

/**
 * Wappen im Feuerwehrgebrauch- Wartung (Nachfolger von VF_PS_OV_FW_Edit.php), Tabelle psw_ort_wappen
 *
 * Ansehen: jeder angemeldete Benutzer (schreibgeschützt), extern gesperrt.
 * Ändern/Anlegen: ADM-PSA (AccessGuard::canEditPsa), CSRF-Token, nur benannte Felder.
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
register_shutdown_function(function () use ($systemBasePath) {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        file_put_contents(__DIR__ . '/FWA_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/FWA_Edit_php-error.log.txt');

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Auth\AccessGuard;
use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Services\HTMLPage;

$module = 'PSA-ORT';
$sub_mod = 'Edit';

$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

const FWA_TABLE = 'psw_ff_wappen';

// ============================================================================================================
// RBAC - VOR jeder Ausgabe
// ============================================================================================================
$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
$isPublicView = AccessGuard::isPublicView();
// Sub-Edit ohne externe Detailansicht: intern jeder Angemeldete (schreibgeschützt), extern gesperrt
if (!$guard->canViewEditForm('FW', $isPublicView)) {
    AccessGuard::deny('Keine Berechtigung.');
}
$canEdit = $guard->canEditPsa();   // false in der externen Ansicht

$phase = (int) ($_POST['phase'] ?? 0);
$fo_id = (int) ($_POST['fo_id'] ?? $_GET['ID'] ?? 0);                 // Record-Nr
$fo_fw_id = (int) ($_POST['fo_fw_id'] ?? $_GET['fo_fw_id'] ?? 0);     // Ort (nur bei Neuanlage maßgeblich)
$proj = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($_POST['proj'] ?? $_GET['proj'] ?? 'AERM'));

if (!$canEdit && ($phase === 1 || $fo_id === 0)) {
    AccessGuard::deny($fo_id === 0 ? 'Zum Anlegen eines Feuerwehr-Wappens ist die Berechtigung ADM-PSA erforderlich.' : 'Änderungen sind nicht erlaubt.');
}
if ($phase === 1) {
    AccessGuard::requireCsrf();   // Token aus dem Formular (FWAEdit_ph0_inc.php)
}

$pdo = $DBD->getPDO();

// Bestehender Datensatz bzw. Ort, dem ein neuer zugeordnet wird
$current = null;
if ($fo_id > 0) {
    $st = $pdo->prepare('SELECT * FROM ' . FWA_TABLE . ' WHERE fo_id = :fo_id');
    $st->execute(['fo_id' => $fo_id]);
    $current = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($current === null) {
        AccessGuard::deny('Wappen im Feuerwehrgebrauch nicht gefunden.', 404);
    }
    $fo_fw_id = (int) $current['fo_fw_id'];   // gehört immer zu seinem Ort, nie aus dem Request
} else {
    $st = $pdo->prepare('SELECT fw_id FROM pso_ort_ref WHERE fw_id = :fw_id');
    $st->execute(['fw_id' => $fo_fw_id]);
    if ($st->fetchColumn() === false) {
        AccessGuard::deny('Der zugeordnete Ort wurde nicht gefunden.', 400);
    }
}

// ============================================================================================================
// Phase 1: Speichern (Rechte und CSRF sind oben geprüft)
// ============================================================================================================
if ($phase === 1) {
    $sortOrder = filter_var($_POST['fo_ff_w_sort'] ?? 5, FILTER_VALIDATE_INT);
    $comment = $_POST['fo_ff_w_komm'] ?? '';
    if ($sortOrder === false || !is_string($comment)) {
        AccessGuard::deny('Ungültige Formularwerte.', 400);
    }

    date_default_timezone_set('Europe/Berlin');
    $saved = [
        'fo_ff_w_sort'    => $sortOrder,
        'fo_ff_w_komm' => trim($comment),
        'fo_changed_id'    => $guard->userId(),
        'fo_changed_at'    => date('Y-m-d H:i:s'),
    ];

    if ($fo_id === 0) {
        $DBD->insert(FWA_TABLE, ['fo_fw_id' => $fo_fw_id, 'fo_ff_wappen' => ''] + $saved);
    } else {
        $DBD->update(FWA_TABLE, $saved, ['fo_id' => $fo_id]);   // Bilddatei und Ort bleiben unverändert
    }

    header('Location: OrtsEdit.php?ID=' . $fo_fw_id . '&proj=' . rawurlencode($proj));
    exit;
}

// ============================================================================================================
// Seite
// ============================================================================================================
echo "<!doctype html>";

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('Feuerwehr- Wappen - Verwaltung',
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
$columnsByTables = $meta->getColumnsForTables(['pso_ort_ref', 'psw_aermel_abz', 'psw_ff_wappen', 'psw_ff_wappen' ]);

if ($current === null) {
    $neu = [
        'fo_id' => 0,
        'fo_fw_id' => $fo_fw_id,
        'fo_ff_wappen' => '',
        'fo_ff_w_sort' => 5,
        'fo_ff_w_komm' => '',
        'fo_changed_id' => 0,
        'fo_changed_at' => '',
    ];
} else {
    $neu = $current;
}

$editProtect = !$canEdit;
require 'FWAEdit_ph0_inc.php';

$page->renderFooter();
?>
