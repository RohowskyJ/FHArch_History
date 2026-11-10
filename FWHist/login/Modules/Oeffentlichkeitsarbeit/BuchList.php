<?php

declare(strict_types=1);

/**
 * Liste der Buchbesprechungen
 *
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../AordVerz/BuchsList_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../AordVerz/BuchList_php-error.log.txt');
# var_dump($_SERVER);

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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
require $systemBasePath . 'vendor/autoload.php';

echo "<!DOCTYPE html>";

use FWHist\Core\EnvSessionManager;
use FWHist\Services\HTMLPage;

$module = 'OEF-BU';
$sub_mod = 'LIST';
$isPublicView = ($_SESSION['BS_Prim']['Mod']['smod'] ?? '') === 'index';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

if (!$isPublicView && empty($_SESSION['BS_Prim']['BE']['be_id'])) {
    http_response_code(403);
    exit('Anmeldung erforderlich.');
}
$accessGuard = \FWHist\Auth\AccessGuard::current();
$canEdit = $accessGuard->canEditOeffentlichkeit();
$actionConfig = $accessGuard->listActionConfig($canEdit, true, true, $isPublicView);

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
$configFileS = $envManager->getEnv('configFileS');

$header =   "";
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
$ListHead = "Buchbesprechungen";
$title = "Buchbesprechungen";

$page = new HTMLPage('Buchbesprechungen',
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

$newRecordUrl = $canEdit ? 'BuchEdit.php?ID=0' : '';
$lists = [
    [
        'id' => 'BU',
        'container' => 'BU-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Buchbesprechungen',
        'editScript' => $newRecordUrl,
        'actionScript' => 'BuchEdit.php',
        'actionLabel' => $actionConfig['actionLabel'],
        'newLabel' => 'Neuen Bucheintrag anlegen',
        'showActionColumn' => $actionConfig['showActionColumn'],
        'filterSelectFields' => [],
        'filterTextFields' => ['bu_titel', 'bu_teaser', 'bu_author', 'bu_verlag'],
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

<div id="root-BU">
    <h2>Buchbesprechungen</h2>
    <div id="BU-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();
?>
