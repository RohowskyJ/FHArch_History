<?php

declare(strict_types=1);

/**
 * Mitglieder-Ehrungen
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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
require $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Auth\AccessGuard;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\HTMLPage;

$module = 'ADM-List';
$sub_mod = "LIST";

$tabelle = 'mi_ehrungen';// <?php

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MitgliederEhrung_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MI_MitglEhrungsList_php-error.log.txt');
# var_dump($_SERVER);
     
require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
 /*   
use FWHist\Database\DB_GenericLog;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;
    
*/
$guard = AccessGuard::fromSession(new DB_GenericLog());
$canEdit = $guard->canWriteMemberData();
if (!$canEdit) {
    http_response_code(403);
    exit('Für diesen Programmteil besteht keine Berechtigung.');
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
$configFileS = $envManager->getEnv('configFileS');
$page = new HTMLPage(
    'Mitglieder-Ehrungen',
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

$memberId = filter_input(INPUT_GET, 'mi_id', FILTER_VALIDATE_INT);
$memberId = $memberId !== false && $memberId !== null && $memberId > 0 ? $memberId : null;

$lists = [
    [
        'id' => 'MIE',
        'container' => 'MIE-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Alle Ehrungen',
        'editScript' => '',
        'actionScript' => $canEdit ? 'MitglEhrgEdit.php' : '',
        'actionLabel' => 'Bearbeiten',
        'newLabel' => '',
        'showActionColumn' => $canEdit,
        'filterTextFields' => ['me_ehrung', 'me_begruendg'],
        'paginationSizes' => [10, 25, 50, 100],
    ],
];

$globalParams = [
    'owner' => 'Mitglieder-Ehrungen',
    'user' => $_SESSION['BS_Prim']['BE']['be_id'] ?? null,
    'project' => 'Alle',
    'sel_id' => $memberId,
    'edit_Protect' => !$canEdit,
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.lists = lists;
globalThis.globalParams = globalParams;
</script>

<div id="root-MIE">
    <h2>Alle Ehrungen</h2>
    <div id="MIE-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();
?>
