<?php

declare(strict_types=1);

/**
 * Benutzerverwaltung - Liste
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfen, ob Session gültig ist
if (empty($_SESSION['BS_Prim'])) {
    // Session ungültig ? Weiterleitung auf externe URL
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        header('Location: /localhost/Feuerwehr_Archiv/FWHist/');
        exit;
    }
    header('Location: https://www.feuerwehrhistoriker.at/FWHist/');
    exit;
}

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
require $systemBasePath . 'vendor/autoload.php';

use FWHist\Auth\AccessGuard;
use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\HTMLPage;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/Benutzer_fatal_error.log', $message, FILE_APPEND);
    }
});

$tabelle = 'fv_benutzer';// <?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/Ben_List_php-error.log.txt');
# var_dump($_SERVER);
    
$module = 'ADM-Benu';
$sub_mod = "LIST";

$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

// RBAC: Benutzerverwaltung nur für ADM-ZUGR (und ADM-ALLE) - VOR jeder Ausgabe
$guard = AccessGuard::fromSession(new DB_GenericLog());
if (!$guard->canManageAccess()) {
    AccessGuard::deny('Für die Benutzerverwaltung ist die Berechtigung ADM-ZUGR erforderlich.');
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
$configFileS = $envManager->getEnv('configFileS');
$page = new HTMLPage(
    'Benutzerverwaltung',
    $systemBasePath,
    $basePath,
    [
        'headExtras' => '',
        'type' => 'Form',
        'width' => '120em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

$lists = [
    [
        'id' => 'BE',
        'container' => 'BE-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Benutzerverwaltung',
        'editScript' => 'BenEdit.php',
        'actionScript' => 'BenEdit.php',
        'actionLabel' => 'Bearbeiten',
        'newLabel' => 'Neuen Benutzer anlegen',
        'filterSelectFields' => ['be_status'],
        'filterTextFields' => ['fd_name', 'fd_ort', 'fd_email'],
        'paginationSizes' => [10, 25, 50, 100],
    ],
];

$globalParams = [
    'owner' => 'Benutzerverwaltung',
    'user' => $_SESSION['BS_Prim']['BE']['be_id'] ?? null,
    'project' => null,
    'sel_id' => null,
    'edit_Protect' => !$guard->canManageAccess(),
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.lists = lists;
globalThis.globalParams = globalParams;
</script>

<div id="root-BE">
    <h2>Benutzerverwaltung</h2>
    <div id="BE-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();
?>
