<?php

declare(strict_types=1);

/**
 * Termine- Liste
 *
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
require $systemBasePath . 'vendor/autoload.php';

echo "<!DOCTYPE html>";

use FWHist\Core\EnvSessionManager;
use FWHist\Services\HTMLPage;

$module = 'OEF-TE';
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
    'Termine',
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
$newRecordUrl = $canEdit ? 'TerminEdit.php?ID=0' : '';
$lists = [
    [
        'id' => 'TE',
        'container' => 'TE-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Termine',
        'editScript' => $newRecordUrl,
        'actionScript' => 'TerminEdit.php',
        'actionLabel' => $actionConfig['actionLabel'],
        'newLabel' => 'Neuen Termin anlegen',
        'showActionColumn' => $actionConfig['showActionColumn'],
        'dateFilter' => [
            'label' => 'Veranstaltungsdatum',
            'startField' => 'va_datum',
            'endField' => 'va_end_dat',
            'today' => date('Y-m-d'),
        ],
        'filterSelectFields' => [],
        'filterTextFields' => ['va_datum', 'va_titel', 'va_beschr'],
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

<div id="root-TE">
    <h2>Termine</h2>
    <div id="TE-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();
?>
