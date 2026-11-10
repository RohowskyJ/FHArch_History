<?php

declare(strict_types=1);

/**
 * Marktplatz- Liste
 * 
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
 * 
 * 
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
        file_put_contents(__DIR__ . '/../../AOrd_Verz/MP_sList_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../AOrd_Verz/MP_List_php-error.log.txt');
# var_

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

// Projekt-/Session-Umgebung initialisieren.
$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
// Composer-Autoloader, bevor eigentliche Klassen verwendet werden.
require $systemBasePath . 'vendor/autoload.php';  //  __DIR__ == C:\JProgs\htdocs\Feuerwehr_Archiv\FWHist\login\Controllers; dirname(__DIR__, 2) == C:\JProgs\htdocs\Feuerwehr_Archiv\FWHist\

echo "<!DOCTYPE html>";

use FWHist\Core\EnvSessionManager;
use FWHist\Services\HTMLPage;

$module = 'OEF-AN';
$sub_mod = "LIST";
$isPublicView = ($_SESSION['BS_Prim']['Mod']['smod'] ?? '') === 'index';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

if (!$isPublicView && empty($_SESSION['BS_Prim']['BE']['be_id'])) {
    http_response_code(403);
    exit('Anmeldung erforderlich.');
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header =   "";
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================
#$ListHead = "Biete/Suche - Marktplatz";
#$title = "Biete/Suche - Marktplatz";

$page = new HTMLPage('Biete/Suche - Marktplatz',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
    'width' => '90em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

$moduleId = $module."-".$sub_mod;
// Eigene Meldung mit Modulkennung loggen
# $logger->log('Starte Verarbeitung des Moduls', $moduleId, basename(__FILE__));

$accessGuard = \FWHist\Auth\AccessGuard::current();
$canEdit = $accessGuard->canEditOeffentlichkeit();
$actionConfig = $accessGuard->listActionConfig($canEdit, true, true, $isPublicView);
$newRecordUrl = $canEdit ? 'MarktplEdit.php?ID=0' : '';
$lists = [
    [
        'id' => 'MP',
        'container' => 'MP-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Biete/Suche - Marktplatz',
        'editScript' => $newRecordUrl,
        'actionScript' => 'MarktplEdit.php',
        'actionLabel' => $actionConfig['actionLabel'],
        'newLabel' => 'Neuen Marktplatz-Eintrag anlegen',
        'showActionColumn' => $actionConfig['showActionColumn'],
        'filterSelectFields' => [],
        'filterTextFields' => ['bs_kurztext', 'bs_text', 'bs_email_1'],
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

<div id="root-MP">
    <h2>Biete/Suche - Marktplatz</h2>
    <div id="MP-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();

?>