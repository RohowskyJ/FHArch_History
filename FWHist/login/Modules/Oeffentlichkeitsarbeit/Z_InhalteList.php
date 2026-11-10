<?php

declare(strict_types=1);

/**
 * Zeitungs-Inhaltsliste
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

$module = 'OEF-ZT';
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
if (!$accessGuard->canViewZeitungsContent()) {
    \FWHist\Auth\AccessGuard::deny('Für diese Liste besteht keine Berechtigung.');
}

$zeitungsIdValue = $_GET['ID'] ?? $_POST['zt_id'] ?? null;
$zeitungsId = filter_var(
    $zeitungsIdValue,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
$configFileS = $envManager->getEnv('configFileS');
$page = new HTMLPage(
    'Zeitungs-Inhalte',
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

if ($zeitungsId === false) {
    echo '<div class="error-box">Keine gültige Zeitungs-ID übergeben; die Inhalte können nicht angezeigt werden.</div>';
    $page->renderFooter();
    exit;
}

$canEdit = $accessGuard->canEditOeffentlichkeit();
$canViewReadOnly = $accessGuard->canViewZeitungsContent() && !$canEdit;
$newRecordUrl = $canEdit
    ? 'Z_InhalteEdit.php?ID=0&ztNr=' . $zeitungsId
    : '';
$lists = [
    [
        'id' => 'ZI',
        'container' => 'ZI-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Zeitungs-Inhalte',
        'editScript' => $newRecordUrl,
        'actionScript' => 'Z_InhalteEdit.php',
        'actionLabel' => $canEdit ? 'Bearbeiten' : 'Anzeigen',
        'newLabel' => 'Neuen Inhalt anlegen',
        'showActionColumn' => $canEdit || $canViewReadOnly,
        'filterSelectFields' => [],
        'filterTextFields' => ['ih_jahr', 'ih_kateg', 'ih_sg', 'ih_titel', 'ih_autor'],
        'paginationSizes' => [10, 25, 50, 100],
    ],
];

$globalParams = [
    'user' => $_SESSION['BS_Prim']['BE']['be_id'] ?? null,
    'project' => null,
    'sel_id' => $zeitungsId,
    'edit_Protect' => !$canEdit,
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.lists = lists;
globalThis.globalParams = globalParams;
</script>

<div id="root-ZI">
    <h2>Zeitungs-Inhalte</h2>
    <div id="ZI-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();
?>
