<?php

declare(strict_types=1);

/**
 * Dokumente- Liste, vom / für den Verein erstellte Dokumente
 *
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
require $systemBasePath . 'vendor/autoload.php';

echo "<!DOCTYPE html>";

use FWHist\Core\EnvSessionManager;
use FWHist\Services\HTMLPage;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MusList_fatal_error.log', $message, FILE_APPEND);
    }
});
    
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MusList_php-error.log.txt');
# var_dump($_SERVER);

$module = 'OEF-DO';
$sub_mod = 'LIST';
$isPublicView = ($_SESSION['BS_Prim']['Mod']['smod'] ?? '') === 'index';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

if (!$isPublicView && empty($_SESSION['BS_Prim']['BE']['be_id'])) {
    http_response_code(403);
    exit('Anmeldung erforderlich.');
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
$configFileS = $envManager->getEnv('configFileS');

$page = new HTMLPage(
    'Dokumentationen des Vereines',
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

$accessGuard = \FWHist\Auth\AccessGuard::current();
$canEdit = $accessGuard->canEditOeffentlichkeit();
$actionConfig = $accessGuard->listActionConfig($canEdit, true, true, $isPublicView);
$newRecordUrl = $canEdit ? 'DokuEdit.php?ID=0' : '';
$lists = [
    [
        'id' => 'DO',
        'container' => 'DO-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Dokumentationen des Vereines',
        'editScript' => $newRecordUrl,
        'actionScript' => 'DokuEdit.php',
        'actionLabel' => $actionConfig['actionLabel'],
        'newLabel' => 'Neue Dokumentation anlegen',
        'showActionColumn' => $canEdit,
        'filterSelectFields' => [],
        'filterTextFields' => ['dk_thema', 'dk_titel', 'dk_author'],
        'paginationSizes' => [10, 25, 50, 100],
    ],
];

$globalParams = [
    'user' => $_SESSION['BS_Prim']['BE']['be_id'] ?? null,
    'project' => null,
    'sel_id' => null,
    'edit_Protect' => $actionConfig['editProtect'],
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.lists = lists;
globalThis.globalParams = globalParams;
</script>

<div id="root-DO">
    <h2>Dokumentationen des Vereines</h2>
    <div id="DO-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();
?>
