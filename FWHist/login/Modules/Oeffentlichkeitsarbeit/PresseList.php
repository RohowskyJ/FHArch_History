<?php

declare(strict_types=1);

/**
 * Pressespiegel - Liste
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['BS_Prim'])) {
    if (($_SERVER['HTTP_HOST'] ?? '') === 'localhost') {
        header('Location: /localhost/Feuerwehr_Archiv/FWHist/');
        exit;
    }
    header('Location: https://www.feuerwehrhistoriker.at/FWHist/');
    exit;
}

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
require $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Services\HTMLPage;

$module = 'OEF-AN';
$sub_mod = 'LIST';
$isPublicView = ($_SESSION['BS_Prim']['Mod']['smod'] ?? '') === 'index';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/PRList_php-error.log.txt');

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
    'Presse-Informationen',
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

$accessGuard = \FWHist\Auth\AccessGuard::current();
$canEdit = $accessGuard->canEditOeffentlichkeit();
$actionConfig = $accessGuard->listActionConfig($canEdit, true, true, $isPublicView);
$ActionScript = '';
$NeuRec = NULL;
if ($canEdit) {
    $NeuRec = "PresseEdit.php?ID=0";
    $ActionScript = 'PresseEdit.php';
}

$lists = [
    [
        'id' => 'PR',
        'container' => 'PR-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Presse-Informationen',
        'editScript' => $NeuRec,
        'actionScript' => $ActionScript,
        'actionLabel' => $actionConfig['actionLabel'],
        'newLabel' => 'Neuen Datensatz anlegen',
        'filterTextFields' => ['pr_datum', 'pr_name', 'pr_ausg', 'pr_teaser', 'pr_text'],
        'paginationSizes' => [10, 25, 50, 100],
    ],
];

$globalParams = [
    'owner' => 'Pressespiegel',
    'user' => $_SESSION['BS_Prim']['BE']['be_id'] ?? null,
    'project' => null,
    'sel_id' => null,
    'edit_Protect' => $actionConfig['editProtect'],
    'showActionColumn' => $actionConfig['showActionColumn'],
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.lists = lists;
globalThis.globalParams = globalParams;
</script>

<div id="root-PR">
    <h2>Presse-Informationen</h2>
    <div id="PR-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();
