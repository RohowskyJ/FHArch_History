<?php

/**
 * Rollen für Benutzer zuordnen: Verwaltung, Wartung
 *
 * Zugriff nur mit ADM-ZUGR (oder ADM-ALLE). Vergeben, ändern oder entziehen darf man nur Rollen, die man selbst
 * vergeben darf (ADM-ZUGR: alle außer ADM-ALLE, siehe AccessGuard::canGrantRoleModules).
 *
 * @author Josef Rohowsky - neu 2020
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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function () use ($systemBasePath) {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents($systemBasePath . 'login/AOrd_Verz/RollenEdit_fatal_error.log', $message, FILE_APPEND);
    }
});

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Auth\AccessGuard;
use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_Benutzer;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Services\HTMLPage;

$module = 'ADM-Rolle';
$sub_mod = 'Edit';

$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

// ============================================================================================================
// RBAC - VOR jeder Ausgabe
// ============================================================================================================
$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canManageAccess()) {
    AccessGuard::deny('Für die Rollenvergabe ist die Berechtigung ADM-ZUGR erforderlich.');
}

$phase = (int) ($_POST['phase'] ?? 0);
if ($phase === 1) {
    AccessGuard::requireCsrf();   // Token aus dem Formular (RollenEdit_ph0_inc.php)
}

$pdo = $DBD->getPDO();
$benu = new DB_Benutzer(DB_GenericLog::getInstance());

// Eingaben: Zuordnung (fr_id) und Benutzer (be_id) - beides wird gegen die Datenbank geprüft
$fr_id = (int) ($_POST['fr_id'] ?? $_GET['ID'] ?? 0);
$be_id = isset($_GET['beId']) ? (int) $_GET['beId'] : (int) ($_POST['be_id'] ?? 0);

if ($be_id < 1 || !$benu->userExists($be_id)) {
    AccessGuard::deny('Benutzer nicht gefunden.', 404);
}

// Bestehende Zuordnung: muss zu diesem Benutzer gehören, und ihre Rolle muss der Benutzer vergeben dürfen
$existing = null;
if ($fr_id > 0) {
    $existing = $benu->getRoleById((string) $fr_id, (string) $be_id);
    if ($existing === null) {
        AccessGuard::deny('Rollenzuordnung nicht gefunden.', 404);
    }
    $currentRole = $benu->getRoleDescrById((int) ($existing['fl_id'] ?? 0));
    if (!$guard->canGrantRoleModules((string) ($currentRole['fl_modules'] ?? ''))) {
        AccessGuard::deny('Diese Rollenzuordnung darf nur ein Administrator mit ADM-ALLE ändern.');
    }
}

// Auswahl: nur Rollen, die der Benutzer vergeben darf
$roleDes = array_values(array_filter(
    $benu->getRoleDescrAll() ?? [],
    static fn(array $role): bool => $guard->canGrantRoleModules((string) ($role['fl_modules'] ?? ''))
));
$grantable = [];
foreach ($roleDes as $role) {
    $grantable[(int) $role['fl_id']] = true;
}

date_default_timezone_set('Europe/Berlin');

if ($phase === 1) {
    // Nur diese Werte werden aus dem Formular übernommen; Benutzer, Zeitstempel und Bearbeiter setzt der Server
    $flId = (int) ($_POST['descript'] ?? 0);
    if ($flId < 1) {
        $flId = (int) ($_POST['fl_id'] ?? 0);
    }
    if (!isset($grantable[$flId])) {
        AccessGuard::deny('Diese Rolle darf nicht vergeben werden.');
    }
    $aktiv = (string) ($_POST['fr_aktiv'] ?? 'a');
    if (!in_array($aktiv, ['a', 'i'], true)) {
        AccessGuard::deny('Ungültiger Status.', 400);
    }

    $now = date('Y-m-d H:i:s');
    $by = $guard->userId();
    $neu = ['fl_id' => $flId, 'fr_aktiv' => $aktiv, 'fr_changed_id' => $by, 'fr_changed_at' => $now];
    if ($existing === null) {
        $neu += ['be_id' => $be_id, 'fr_created_id' => $by, 'fr_created_at' => $now];
    }
}

$profileId = $benu->getProfileIdByBeId($be_id);   // Schlüssel von BenEdit.php
$benName = $benu->getUserNameByBeId($be_id);

// ============================================================================================================
// Seite
// ============================================================================================================
echo "<!doctype html>";

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('Benutzer- Verwaltung',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$meta = new TableColumnMetadata($pdo,'fharch_new',false);

$columnsByTables = $meta->getColumnsForTables(['fv_rolle', 'fv_rollen_beschr']);

// ============================================================================================================
// Phase 0: Anzeige
// ============================================================================================================
if ($phase === 0) {
    if ($existing === null) {
        $neu['fr_id'] = 0;
        $neu['be_id'] = $be_id;
        $neu['fl_id'] = $neu['fr_aktiv'] = "";
        $neu['fr_created_id'] = $neu['fr_changed_id'] = 0;
        $neu['fr_created_at'] = $neu['fr_changed_at'] = "0000-00-00 00:00:00";
    } else {
        $neu = $existing;
    }
    $neu['descript'] = "";
    $neu['Rolle'] = "";

    if ($debug) {
        echo '<pre class=debug>';
        echo '<hr>$neu: ';
        var_dump($neu);
        echo '</pre>';
    }
}

switch ($phase) {
    case 0:
        require 'RollenEdit_ph0_inc.php';
        break;
    case 1:
        require "RollenEdit_ph1_inc.php";
        break;
}
$page->renderFooter();
?>
