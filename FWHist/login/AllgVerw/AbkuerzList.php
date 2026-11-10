<?php

declare(strict_types=1);

/**
 * Abkürzungsverwaltung - Liste
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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
require $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Services\HTMLPage;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/Mitglied_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/AbkuerzList_php-error.log.txt');
# var_dump($_SERVER);

$debug = False; // Debug output Ein/Aus Schalter

$module = 'ADM-Abk';
$sub_mod = "LIST";
    
$Zugr = "ADM-AB";
    
$tabelle = '';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

$canEdit = !empty($_SESSION['BS_Prim']['BE']['be_id']) && userHasRole($Zugr);
if (!$canEdit) {
    http_response_code(403);
    exit('Für diesen Programmteil besteht keine Berechtigung.');
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
$configFileS = $envManager->getEnv('configFileS');
$page = new HTMLPage(
    'Abkürzungsverwaltung',
    $systemBasePath,
    $basePath,
    [
        'headExtras' => '',
        'type' => 'Form',
        'width' => '110em',
    ]
);

$page->loadConfig($configFileS);
$page->renderHeader();

$lists = [
    [
        'id' => 'ABK',
        'container' => 'ABK-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Abkürzungsverwaltung',
        'editScript' => 'AbkuerzEdit.php',
        'actionScript' => 'AbkuerzEdit.php',
        'actionLabel' => 'Bearbeiten',
        'newLabel' => 'Neue Abkürzung eingeben',
        'showActionColumn' => $canEdit,
        'filterSelectFields' => ['ab_grp'],
        'filterTextFields' => ['ab_abk', 'ab_bezeichn', 'ab_gruppe'],
        'paginationSizes' => [10, 25, 50, 100],
    ],
];

$globalParams = [
    'owner' => 'Abkürzungsverwaltung',
    'user' => $_SESSION['BS_Prim']['BE']['be_id'] ?? null,
    'project' => null,
    'sel_id' => null,
    'edit_Protect' => !$canEdit,
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.lists = lists;
globalThis.globalParams = globalParams;
</script>

<div id="root-ABK">
    <h2>Abkürzungen</h2>
    <div id="ABK-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();

?>
