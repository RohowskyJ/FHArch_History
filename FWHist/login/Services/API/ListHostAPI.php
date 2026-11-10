<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

/** Shutdown-Funktion direkt am Anfang registrieren */
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../../login/AOrd_Verz/Logging/ListHost_fatal_error.log.txt', $message, FILE_APPEND);
    }
});

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use FWHist\Services\ListResolver;
use FWHist\Database\DB_GenericLog;
use FWHist\Auth\AccessGuard;

$dbLogger = new DB_GenericLog();
$guard = AccessGuard::fromSession($dbLogger);

$listId = $_GET['list_ID'] ?? null;
$action = $_GET['action'] ?? 'data';
$project = $_GET['project'] ?? null;
$editProtectParam = $_GET['edit_Protect'] ?? $_GET['editProtect'] ?? 'false';
$editProtect = !in_array(strtolower((string) $editProtectParam), ['0', 'false', 'off', 'no', ''], true);
$isPublicView = ($_SESSION['BS_Prim']['Mod']['smod'] ?? '') === 'index';
$protectedListRoles = [
    'ABK' => 'ADM-AB',
    'FIR' => 'ADM-FI',
    'EMA' => 'ADM-ALLE',
    'MA' => 'ADM-MA',
    'MI' => 'ADM-MI',
    'MIE' => 'ADM-MI',
    'UN' => 'ADM-MI',
    'NM' => 'ADM-MI',
    'MIB' => 'ADM-MB',
    'BE' => 'ADM-ZUGR',    // Benutzerliste (Namen, Adressen, E-Mail)
    'RO' => 'ADM-ZUGR',    // Rollen je Benutzer
    'ROM' => 'ADM-ZUGR',   // Mandanten-Erlaubnisse je Benutzer
];

// Ohne Login lesbar sind nur die Listen der externen Seiten (Öffentlichkeitsarbeit, Ärmelabzeichen/PSA);
// jede andere Liste verlangt eine gültige Anmeldung.
$publicLists = ['ORT', 'OW', 'FW', 'AWA', 'AL', 'BU', 'DO', 'MP', 'MU', 'PR', 'TE', 'ZT', 'ZI'];
if (
    (!$guard->isLoggedIn() && !in_array($listId, $publicLists, true))
    || ($isPublicView && !in_array($listId, $publicLists, true))
) {
    http_response_code(403);
    echo json_encode(['error' => 'Anmeldung erforderlich.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($listId === 'ZI' && !$guard->canViewZeitungsContent()) {
    http_response_code(403);
    echo json_encode(['error' => 'Für diese Liste besteht keine Berechtigung.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (in_array($listId, ['ORT', 'OW', 'FW', 'AWA'], true)) {
    $systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
    require_once $systemBasePath . 'login/Services/CommFuncsLib.php';
    $editProtect = $isPublicView || !$guard->canEditPsa();
} elseif (in_array($listId, ['AR', 'AD'], true)) {
    $editProtect = $isPublicView || !$guard->canWriteMandantData('ARC', (int) ($_GET['sel_id'] ?? 0));
} elseif ($listId === 'IN') {
    $systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
    require_once $systemBasePath . 'login/Services/CommFuncsLib.php';
    if (empty($_SESSION['BS_Prim']['BE']['be_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Anmeldung erforderlich.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Inventar: Schreibrecht je Mandant (sel_id = ei_id): ADM-INV, oder ONU-ALL mit Mandanten-Erlaubnis
    $editProtect = $editProtect || $isPublicView || !$guard->canWriteMandantData('INV', (int) ($_GET['sel_id'] ?? 0));
} elseif (in_array($listId, ['AL', 'BU', 'DO', 'MP', 'MU', 'PR', 'TE', 'ZT', 'ZI'], true)) {
    $systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
    require_once $systemBasePath . 'login/Services/CommFuncsLib.php';
    $editProtect = $isPublicView || !$guard->canEditOeffentlichkeit();
}

if (is_string($listId) && isset($protectedListRoles[$listId])) {
    $systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
    require_once $systemBasePath . 'login/Services/CommFuncsLib.php';
    if ($isPublicView || !$guard->hasPermission($protectedListRoles[$listId])) {
        http_response_code(403);
        echo json_encode(['error' => 'Für diese Liste besteht keine Berechtigung.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $editProtect = $isPublicView;
}

if ($listId === 'MM' && empty($_SESSION['BS_Prim']['BE']['be_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Anmeldung erforderlich.'], JSON_UNESCAPED_UNICODE);
    exit;
}

#$fwId = isset($_GET['fw_id'])  && $_GET['fw_id'] !== '' ? (int) $_GET['fw_id'] : null; // muss durch selId ersetzt werden (Neutrale Definition
$selId = isset($_GET['sel_id'])  && $_GET['sel_id'] !== '' ? (int) $_GET['sel_id'] : null;

if (in_array($listId, ['FAF', 'FAG'], true)) {
    $validatedSelId = filter_var($_GET['sel_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$guard->canReadMandantData() || $validatedSelId === false) {
        http_response_code(403);
        echo json_encode(['error' => 'Anmeldung und eine gültige Eigentümer-ID sind erforderlich.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $selId = $validatedSelId;
    $editProtect = !$guard->canWriteMandantData('FZG', $selId);
}

if (in_array($listId, ['MV', 'MD'], true)) {
    if (!$guard->canReadMandantData() || $selId === null || $selId < 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Anmeldung und eine gültige Eigentümer-ID sind erforderlich.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $editProtect = !$guard->canWriteMandantData('MEDIA', $selId);
}

#$message = "sel_id $selId ; editProtect $editProtect \n";
#file_put_contents(dirname(__DIR__, 3) . '/login/AOrd_Verz/Logging/ListHost_fatal_error.log.txt', $message, FILE_APPEND);

if (empty($selId) && !in_array($listId, ['ABK', 'AL', 'BE', 'BU', 'DO', 'EMA', 'FIR', 'MA', 'MI', 'MIB', 'MIE', 'MP', 'MU', 'PR', 'TE', 'UN', 'ZT', 'ZI'], true)) {
    throw new InvalidArgumentException('Parameter selId darf nicht leer sein.');
}

$pdo = $dbLogger->getPDO();


try {
    if (!is_string($listId) || $listId === '') {
        throw new InvalidArgumentException('Fehlende list_ID');
    }

    $list = ListResolver::resolve($listId);

    switch ($action) {
        case 'columns':
            $result = $list->getColumns($pdo, is_string($project) ? $project : null, $selId, $editProtect);
            break;

        case 'filters':
            $result = $list->getFilters($pdo, is_string($project) ? $project : null, $selId, $editProtect);
            break;

        case 'data':
            $result = $list->getData($pdo, is_string($project) ? $project : null, $selId, $editProtect);
            break;

        default:
            http_response_code(400);
            $result = ['error' => 'Unbekannte Aktion'];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    http_response_code($exception instanceof InvalidArgumentException ? 400 : 404);
    $error = $exception->getMessage();
    $errorCode = null;
    if (
        $exception instanceof PDOException
        && (($exception->errorInfo[0] ?? $exception->getCode()) === '42S02'
            || (int)($exception->errorInfo[1] ?? 0) === 1146)
    ) {
        $tableSuffix = match ($listId) {
            'FAE' => '_fza_eigner',
            'FAA' => '_fza_dokumente',
            default => null,
        };
        $expectedTable = $listId === 'ZI' && $selId !== null
            ? "oe_zeitung_{$selId}"
            : ($tableSuffix !== null && $selId !== null
            ? "{$selId}{$tableSuffix}"
            : null);
        $error = $expectedTable !== null && str_contains($exception->getMessage(), $expectedTable)
            ? "Keine Tabelle gefunden: {$expectedTable}"
            : 'Keine Tabelle gefunden.';
        $errorCode = 'TABLE_NOT_FOUND';
    }
    echo json_encode(
        array_filter(['error' => $error, 'code' => $errorCode]),
        JSON_UNESCAPED_UNICODE
    );
}