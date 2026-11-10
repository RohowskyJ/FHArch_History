<?php

declare(strict_types=1);

/**
 * Mitglieder können e-Mail an Mitglieder senden, ohne deren e-Mail- Adresse zu kennen
 *
 * Reine Listen-Anzeige (Tabulator über ListHostAPI.php / list-tables.js,
 * Liste NM). Die E_Mail wird über MitglMEdit.php eingegeben und dann  mit PHPMailer verchickt
 *
 * @author Josef Rohowsky - neu 2026 - 
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Auth\AccessGuard;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\HTMLPage;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        file_put_contents(__DIR__ . '/MitglAktivierung_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MitglAktivierung_php-error.log.txt');

$module = 'MVW';
$sub_mod = 'M-M_Mail';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/PHP_MailFuncsLib.php';

$guard = AccessGuard::fromSession(new DB_GenericLog());
if (!$guard->isLoggedIn() || AccessGuard::isPublicView()) {
    header("Location: " . $systemBasePath . "/");
    exit;
}
$canSendEmail = true;

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
$configFileS = $envManager->getEnv('configFileS');

$page = new HTMLPage(
    'E-Mail an andere Mitglieder',
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

$DBD = new DB_GenericLog();
$pdo = $DBD->getPDO();

// Listen-Konfiguration serverseitig definiert
$lists = [
    [
        'id' => 'MM',
        'container' => 'MM-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Offene Mitgliedsanmeldungen',
        'editScript' => $canSendEmail ? 'MitglMEdit.php' : '',
        'newLabel' => '',
        'actionScript' => $canSendEmail ? 'MitglMEdit.php' : '',
        'actionLabel' => 'E-Mail',
        'showActionColumn' => $canSendEmail,
        'filterSelectFields' => [],
        'filterTextFields' => ['mi_name', 'mi_vname', 'mi_ort'],
        'paginationSizes' => [10, 25, 50],
    ],
];

// Globales Parameter-Array (kein fw_id/Mandanten-Bezug bei Mitgliedsanmeldungen)
$globalParams = [
    'owner' => 'E-Mail an andere Mitglieder',
    'user' => $_SESSION['BS_Prim']['BE']['be_uid'] ?? '',
    'project' => null,
    'sel_id' => 9,
    'edit_Protect' => false, // E-Mail muss man eingeben können
];

?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.globalParams = globalParams;
</script>

<div id="root-MM">
    <h2>E-Mail an andere Mitglieder</h2>
    <p>Bei Anklicken des Buttons "Mail an" kann eine E-Mail an dieses Mitglied verschickt werden.</p>
    <div id="MM-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>

<?php
$page->renderFooter();
