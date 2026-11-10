<?php

declare(strict_types=1);

/**
 * MultiSelectAPI.php
 * -------------------
 * Dispatcher für kaskadierbare Mehrfach-Dropdown-Auswahlen (Sammlung,
 * Archivordnung, ...). Bewusst als EIGENER Endpunkt neben ListHostAPI.php,
 * weil der Parameter-Vertrag ein anderer ist:
 *
 *   config_ID   Pflicht. Schlüssel für MultiSelectResolver (z.B. "ARCHIVORDNUNG")
 *   level       Pflicht. 1-basierte Ziel-Ebene (int)
 *   parent      Optional. value der vorherigen Ebene (leer/fehlt bei level=1)
 *   project     Optional. Zusatzkontext, je nach Provider (z.B. Sammlungs-Gruppe)
 *
 * Kein sel_id: Die Optionsliste einer Dropdown-Kaskade hat -- anders als eine
 * Tabulator-Liste -- keinen Bezug zu einem einzelnen Datensatz. Ein leeres
 * sel_id hier zu verlangen (wie in ListHostAPI.php) würde die Verwendung in
 * "Neuanlage"-Formularen (noch kein Datensatz vorhanden) blockieren.
 *
 * Response-Envelope bewusst kompatibel zur alten VF_MultiSel_Opt_jq_API.php
 * gehalten, damit bestehende Frontend-Erwartungen (status/data/error) nicht
 * überall gleichzeitig angepasst werden müssen:
 *   { "status": "ok"|"error", "data": [...], "error": string|null }
 *
 * Platzierung gemäß RepoStructBase.md neben ListHostAPI.php:
 *   login/Services/API/MultiSelectAPI.php
 */

header('Content-Type: application/json; charset=utf-8');

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected (MultiSelectAPI):\n" . print_r($error, true);
        error_log($message);
        file_put_contents(
            __DIR__ . '/../../../login/AOrd_Verz/Logging/MultiSelectAPI_fatal_error.log',
            $message,
            FILE_APPEND
        );
    }
});

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/ConstLib.php';

use FWHist\Database\DB_GenericLog;
use FWHist\Services\MultiSelect\MultiSelectResolver;
# use InvalidArgumentException;

/**
 * AppLogger.php liegt im GLOBALEN Namespace (kein "namespace"-Header in der
 * Datei) und ist damit nicht über PSR-4-Autoloading erreichbar -- deshalb
 * expliziter require. Pfad-ANNAHME: login/Services/AppLogger.php (gleiche
 * Ebene wie login/Services/MultiSelect/, login/Services/API/ liegt eine
 * Ebene tiefer) -- bitte Pfad prüfen/anpassen, falls AppLogger.php woanders
 * liegt.
 */
$appLoggerPath = dirname(__DIR__) . '/AppLogger.php';
if (is_file($appLoggerPath)) {
    require_once $appLoggerPath;
}

function logGeneralError(string $message, array $context = []): void
{
    if (class_exists('AppLogger')) {
        try {
            \AppLogger::error($message, $context);
            return;
        } catch (\Throwable $e) {
            // Fällt durch auf error_log() unten
        }
    }
    error_log('[MultiSelectAPI] ' . $message . ' ' . json_encode($context, JSON_UNESCAPED_UNICODE));
}

$configId = isset($_GET['config_ID']) ? trim((string)$_GET['config_ID']) : '';
$level    = isset($_GET['level']) ? (int)$_GET['level'] : 0;
$parent   = isset($_GET['parent']) && $_GET['parent'] !== '' ? trim((string)$_GET['parent']) : null;
$project  = isset($_GET['project']) && $_GET['project'] !== '' ? trim((string)$_GET['project']) : null;

$result = [
    'status' => 'ok',
    'data'   => [],
    'error'  => null,
];

try {
    if ($configId === '') {
        throw new InvalidArgumentException('Fehlende config_ID');
    }
    if ($level < 1) {
        throw new InvalidArgumentException('Ungültige oder fehlende level');
    }

    $provider = MultiSelectResolver::resolve($configId);

    if ($level > $provider->getLevelCount()) {
        throw new InvalidArgumentException(
            "level $level überschreitet getLevelCount() (" . $provider->getLevelCount() . ") für $configId"
        );
    }
    if ($level > 1 && ($parent === null || $parent === '')) {
        throw new InvalidArgumentException("parent ist für level $level erforderlich");
    }

    $db = new DB_GenericLog();

    $options = $provider->getOptions($db, $level, $parent, $project);

    // Platzhalter-Eintrag zentral hier voranstellen (nicht im Provider),
    // damit Provider reine Datenlogik bleiben.
    $data = [['value' => '', 'text' => 'Bitte wählen']];
    foreach ($options as $opt) {
        $data[] = ['value' => (string)$opt['value'], 'text' => (string)$opt['text']];
    }

    $result['data'] = $data;

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(
        ['status' => 'error', 'data' => [], 'error' => $e->getMessage()],
        JSON_UNESCAPED_UNICODE
    );
} catch (\Throwable $e) {
    logGeneralError('MultiSelectAPI failure', [
        'message'   => $e->getMessage(),
        'config_ID' => $configId,
        'level'     => $level,
        'parent'    => $parent,
    ]);
    http_response_code(500);
    echo json_encode(
        ['status' => 'error', 'data' => [], 'error' => 'Interner Fehler bei der Optionsermittlung'],
        JSON_UNESCAPED_UNICODE
    );
}
