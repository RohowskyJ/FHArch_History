<?php

declare(strict_types=1);

/**
 * Mitgliederverwaltung - Übersicht offener (bestätigter, aber noch nicht
 * aktivierter) Anmeldungen.
 *
 * Reine Listen-Anzeige (Tabulator über ListHostAPI.php / list-tables.js,
 * Liste NM). Die eigentliche Aktivierung passiert in MitglAktivate.php,
 * das über die "Aktivieren"-Spalte pro Zeile aufgerufen wird.
 *
 * @author Josef Rohowsky - neu 2026 - Aktivierungsteil nach MitglAktivate.php ausgelagert 2026-09-25
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
require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Auth\AccessGuard;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\HTMLPage;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        file_put_contents(__DIR__ . '/MitglAktivierung_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MitglAktivierung_php-error.log.txt');

$module = 'MVW';
$sub_mod = 'AKTLIST';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canWriteMemberData()) {
    AccessGuard::deny('Für diesen Programmteil besteht keine Berechtigung.');
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
$configFileS = $envManager->getEnv('configFileS');

$page = new HTMLPage(
    'Mitglieds- Aktivierung',
    $systemBasePath,
    $basePath,
    [
        'headExtras' => '',
        'type' => 'Form',
        'width' => '90em',
    ]
);
$page->loadConfig($configFileS);
$page->renderHeader();

$pdo = $DBD->getPDO();

// Listen-Konfiguration serverseitig definiert
$lists = [
    [
        'id' => 'NM',
        'container' => 'NM-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Offene Mitgliedsanmeldungen',
        'editScript' => '',
        'newLabel' => '',
        'actionScript' => 'MitglAktivate.php',
        'actionLabel' => 'Aktivieren',
        'filterSelectFields' => [],
        'filterTextFields' => ['mi_name', 'mi_vname', 'mi_ort'],
        'paginationSizes' => [10, 25, 50],
    ],
];

// Globales Parameter-Array (kein fw_id/Mandanten-Bezug bei Mitgliedsanmeldungen)
$globalParams = [
    'owner' => 'Mitglieder- Admin',
    'user' => $_SESSION['BS_Prim']['BE']['be_uid'] ?? '',
    'project' => null,
    'sel_id' => 9,
    'edit_Protect' => true, // die Liste selbst ist nicht editierbar, nur "Aktivieren"
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.globalParams = globalParams;
</script>

<div id="root-NM">
    <h2>Offene Mitgliedsanmeldungen</h2>
    <p>Bestätigte Anmeldungen, die noch nicht aktiviert wurden. "Aktivieren" öffnet die Daten zur Kontrolle
       und legt danach Mitglied, Benutzer und Rolle an.</p>
    <div id="NM-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();
