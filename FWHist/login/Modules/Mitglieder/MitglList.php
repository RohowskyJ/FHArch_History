<?php

declare(strict_types=1);

/**
 * Mitgliederverwaltung - Mitgliederliste
 *
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
require $systemBasePath . 'vendor/autoload.php';

echo "<!DOCTYPE html>";

use FWHist\Core\EnvSessionManager;
use FWHist\Auth\AccessGuard;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\HTMLPage;
# use FWHist\Database\DB_GenericLog;

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
    
$module = 'ADM-List';
$sub_mod = "LIST";

$tabelle = 'mi_mitglieder';// <?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MitglList_php-error.log.txt');
# var_dump($_SERVER);

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

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
    'Mitgliederverwaltung',
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
        'id' => 'MI',
        'container' => 'MI-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Mitgliederverwaltung',
        'editScript' => '',
        'actionScript' => 'MitglEdit.php',
        'actionLabel' => 'Bearbeiten',
        'newLabel' => '',
        'filterSelectFields' => ['mi_status'],
        'filterTextFields' => ['mi_name', 'mi_ort', 'mi_email'],
        'paginationSizes' => [10, 25, 50, 100],
    ],
];

$globalParams = [
    'owner' => 'Mitglieder-Administration',
    'user' => $_SESSION['BS_Prim']['BE']['be_id'] ?? null,
    'project' => null,
    'sel_id' => null,
    'edit_Protect' => false,
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.lists = lists;
globalThis.globalParams = globalParams;
</script>

<div id="root-MI">
    <h2>Mitgliederverwaltung</h2>
    <div id="MI-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();
?>
