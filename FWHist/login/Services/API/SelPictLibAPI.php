<?php
/**
 * SelPictLibAPI.php  (login/Services/API/SelPictLibAPI.php)
 *
 * Liefert die Fotos der Medien-Bibliothek zur Auswahl ("Aus Bibliothek auswählen").
 *  - Datenquelle sind die Tabellen {urh_nr}_dmedien (Medien_Verwaltung.md); Zugriff über PDO mit
 *    Prepared Statements (MedienDbLib.php).
 *  - Ablageort laut Foto-Lib: AOrd_Verz/{urh_nr}/09/06/{aufn_datum}/{dateiname}
 *
 * POST-Parameter (alle optional, mindestens einer von urhNr/aufnDat/sammlg/eigner nötig):
 *   urhNr    Urheber-Nr. -> nur die Tabelle dieses Urhebers; ohne Angabe werden alle Tabellen durchsucht
 *   aufnDat  Aufnahmedatum (= Verzeichnis)
 *   sammlg   Sammlung (Teiltext); MEHRERE Begriffe (Komma, Semikolon, Zeilenumbruch) werden mit ODER verknüpft
 *   eigner   Fahrzeug-/Eigner-Nr. (md_fw_id, Teiltext)
 *   berID    Nr. der Buchbesprechung: ergänzt bereits vergebene Werte (vb_ber_detail_4)
 *   debug    '1' -> Antwort enthält zusätzlich "debug" (empfangene Parameter, Tabellen, SQL, Trefferzahlen,
 *            verworfene Dateien); zusätzlich Log in AOrd_Verz/Logging/SelPict_debug.log.txt
 *            (Aufruf auch per GET möglich: SelPictLibAPI.php?debug=1&sammlg=Begriff)
 *
 * Antwort: { status, files: [{flNr, page, pos, titel, dateiname, pfad, url, urhNr, aufnDat, beschreibung}],
 *            errors, [debug] }
 *   Bei Fehlern: { status:'error', message, [debug] } - das Frontend zeigt message an.
 *   pfad = wie bisher relativ "AOrd_Verz/..."; url = Pfad ab Webroot (unabhängig von der aufrufenden Seite)
 *
 * Änderungen 2026-09-29:
 *  - Debug-Möglichkeiten (siehe Parameter debug), Fatal Errors landen im Debug-Log.
 *  - sammlg: mehrere Begriffe als ODER-Bedingung.
 *  - json_encode mit JSON_INVALID_UTF8_SUBSTITUTE (ungültiges UTF-8 in md_beschreibg erzeugte sonst
 *    eine LEERE Antwort).
 *  - PDO ERRMODE_EXCEPTION erzwungen (sonst scheiterten prepare/execute stillschweigend).
 *  - Altes Debug-Schreiben in SelPict_fatal_error.log (relativer Pfad) entfernt.
 */

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
ini_set('log_errors', 1);
$logDir = dirname(__DIR__, 2) . '/AOrd_Verz/Logging';
@mkdir($logDir, 0755, true);
ini_set('error_log', $logDir . '/SelPictLibAPI_php-error.log.txt');

require_once dirname(__DIR__, 1) . '/ConstLib.php';      // GrafFiles
require_once dirname(__DIR__, 1) . '/MedienDbLib.php';   // medienGetPdo() -> FWHist\Database\DB_GenericLog

const SELPICT_MAX_ZEILEN = 500; // je Tabelle, neueste zuerst
const SELPICT_JSON_FLAGS = JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE;

$debugOn = (($_POST['debug'] ?? $_GET['debug'] ?? '') === '1');
$dbg = [
    'post'             => $_POST,
    'get'              => $_GET,
    'tabellen_alle'    => [],
    'tabellen_genutzt' => [],
    'abfragen'         => [],
    'verworfen_ext'    => [],
    'web_basis'        => '',
];

function selpictLog(string $msg): void
{
    global $debugOn, $logDir;
    if ($debugOn) {
        @file_put_contents($logDir . '/SelPict_debug.log.txt', date('Y-m-d H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
    }
}

// Fatal Errors (z.B. Parse-/Typfehler) sonst nur als leere Antwort sichtbar -> ins Debug-Log
register_shutdown_function(static function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        selpictLog('FATAL: ' . $e['message'] . ' @ ' . basename($e['file']) . ':' . $e['line']);
    }
});

function antwortFehler(string $text): void
{
    global $debugOn, $dbg;
    selpictLog('FEHLER: ' . $text);
    $a = ['status' => 'error', 'message' => $text];
    if ($debugOn) { $a['debug'] = $dbg; }
    echo json_encode($a, SELPICT_JSON_FLAGS);
    exit;
}

/** Sonderzeichen für LIKE entschärfen (% und _ aus Benutzereingaben). */
function likeTeil(string $s): string
{
    return '%' . addcslashes($s, '%_\\') . '%';
}

$urhNr   = trim((string) ($_POST['urhNr'] ?? $_GET['urhNr'] ?? ''));
$aufnDat = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_POST['aufnDat'] ?? $_GET['aufnDat'] ?? ''));
$sammlg  = trim((string) ($_POST['sammlg'] ?? $_GET['sammlg'] ?? ''));
$fwId    = trim((string) ($_POST['eigner'] ?? $_GET['eigner'] ?? ''));
$berID   = (int) ($_POST['berID'] ?? $_GET['berID'] ?? 0);

selpictLog("START urhNr='$urhNr' aufnDat='$aufnDat' sammlg='$sammlg' eigner='$fwId' berID=$berID");

if ($urhNr === '' && $aufnDat === '' && $sammlg === '' && $fwId === '') {
    antwortFehler('Keine Suchangabe empfangen: bitte Urheber, Aufnahmedatum oder Sammlung angeben.');
}
if ($urhNr !== '' && (!ctype_digit($urhNr) || strlen($urhNr) > 10)) {
    antwortFehler('Ungültige Urheber-Nr.');
}

// PDO-Verbindung: DB_GenericLog->getPDO() (siehe medienGetPdo() in MedienDbLib.php)
$pdo = medienGetPdo();
if (!$pdo) {
    antwortFehler('Datenbank kann nicht geöffnet werden - Abbruch.');
}
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Web-Pfad von AOrd_Verz ab Document-Root (für <img src>), leer wenn nicht ermittelbar
$aordReal = str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2) . '/AOrd_Verz'));
$docRoot  = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$webBasis = ($docRoot !== '' && stripos($aordReal, $docRoot) === 0) ? substr($aordReal, strlen($docRoot)) . '/' : '';
$dbg['web_basis'] = ['aordReal' => $aordReal, 'docRoot' => $docRoot, 'webBasis' => $webBasis];

$response = ['status' => 'ok', 'files' => [], 'errors' => []];

try {
    // vorhandene {urh_nr}_dmedien-Tabellen (Namen kommen aus der DB und werden zusätzlich geprüft)
    $alle = $pdo->query("SELECT table_name FROM information_schema.tables
                         WHERE table_schema = DATABASE() AND table_name REGEXP '^[0-9]+_dmedien\$'")
                ->fetchAll(PDO::FETCH_COLUMN);
    $tabellen = ($urhNr !== '')
        ? array_values(array_filter($alle, static fn($t) => $t === $urhNr . '_dmedien'))
        : $alle;
    $dbg['tabellen_alle']    = $alle;
    $dbg['tabellen_genutzt'] = $tabellen;
    selpictLog('Tabellen gefunden: ' . implode(',', $alle) . ' | genutzt: ' . implode(',', $tabellen));

    // Vergebene Werte der Buchbesprechung (nur wenn berID angegeben und Tabelle vorhanden)
    $stBer = null;
    if ($berID > 0) {
        try {
            $stBer = $pdo->prepare('SELECT vd_flnr, vd_unter, vd_suffix, vd_titel, vd_beschreibung
                                    FROM vb_ber_detail_4 WHERE vb_flnr = ? AND vd_foto = ?');
        } catch (Throwable $e) {
            error_log('SelPictLibAPI: vb_ber_detail_4 nicht lesbar: ' . $e->getMessage());
            $dbg['ber_fehler'] = $e->getMessage();
        }
    }

    foreach ($tabellen as $tn) {
        if (!preg_match('/^(\d+)_dmedien$/', $tn, $m)) {
            continue;
        }
        $urhTab = $m[1];

        $abfrage = function (array $bed, array $par) use ($pdo, $tn, &$dbg) {
            $sql = "SELECT md_dsn_1, md_aufn_datum, md_beschreibg FROM `$tn`
                    WHERE md_dsn_1 <> '0_Verz'" . ($bed ? ' AND ' . implode(' AND ', $bed) : '') . '
                    ORDER BY md_aufn_datum DESC, md_id DESC LIMIT ' . SELPICT_MAX_ZEILEN;
            $st = $pdo->prepare($sql);
            $st->execute($par);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            $dbg['abfragen'][] = [
                'tabelle' => $tn,
                'sql'     => preg_replace('/\s+/', ' ', $sql),
                'params'  => $par,
                'treffer' => count($rows),
            ];
            selpictLog("Abfrage $tn: " . count($rows) . ' Treffer, params=' . json_encode($par, SELPICT_JSON_FLAGS));
            return $rows;
        };

        $bed = [];
        $par = [];
        if ($aufnDat !== '') { $bed[] = 'md_aufn_datum = ?'; $par[] = $aufnDat; }
        if ($sammlg !== '') {
            // mehrere Begriffe (Komma/Semikolon/Zeilenumbruch) -> ODER
            $terme = array_values(array_filter(array_map('trim', preg_split('/[,;\r\n]+/', $sammlg)), 'strlen'));
            if ($terme) {
                $bed[] = '(' . implode(' OR ', array_fill(0, count($terme), 'md_sammlg LIKE ?')) . ')';
                foreach ($terme as $t) { $par[] = likeTeil($t); }
            }
        }
        if ($fwId !== '') { $bed[] = 'md_fw_id LIKE ?'; $par[] = likeTeil($fwId); }
        $zeilen = $abfrage($bed, $par);

        // wie bisher: ohne Treffer bei längerem Sammlungsnamen nur nach Fahrzeug-/Eigner-Nr. suchen
        if (!$zeilen && $aufnDat === '' && strlen($sammlg) > 4 && $fwId !== '') {
            $zeilen = $abfrage(['md_fw_id LIKE ?'], [likeTeil($fwId)]);
        }

        foreach ($zeilen as $z) {
            $dsn = (string) $z['md_dsn_1'];
            if (!in_array(strtolower(pathinfo($dsn, PATHINFO_EXTENSION)), GrafFiles, true)) {
                $dbg['verworfen_ext'][] = "$urhTab: $dsn"; // Bibliothek zeigt nur Bilder
                continue;
            }
            $aufn = (string) ($z['md_aufn_datum'] ?? '');
            $rel  = "AOrd_Verz/$urhTab/09/06/" . ($aufn !== '' ? "$aufn/" : '') . $dsn;

            $flNr = 0; $page = 0; $pos = 0; $titel = '';
            $beschr = (string) ($z['md_beschreibg'] ?? '');
            if ($stBer) {
                $stBer->execute([$berID, $dsn]);
                if ($b = $stBer->fetch(PDO::FETCH_ASSOC)) {
                    $flNr  = $b['vd_flnr'];
                    $page  = $b['vd_unter'];
                    $pos   = $b['vd_suffix'];
                    $titel = $b['vd_titel'];
                    if (($b['vd_beschreibung'] ?? '') !== '') { $beschr = $b['vd_beschreibung']; }
                }
            }

            $response['files'][] = [
                'flNr'         => $flNr,
                'page'         => $page,
                'pos'          => $pos,
                'titel'        => $titel,
                'dateiname'    => $dsn,
                'pfad'         => $rel,
                'url'          => $webBasis !== '' ? $webBasis . substr($rel, strlen('AOrd_Verz/')) : '',
                'urhNr'        => $urhTab,
                'aufnDat'      => $aufn,
                'beschreibung' => $beschr,
            ];
        }
    }
} catch (Throwable $e) {
    error_log('SelPictLibAPI: ' . $e->getMessage());
    $dbg['exception'] = get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine();
    antwortFehler('Fehler bei der Bibliotheks-Abfrage' . ($debugOn ? ' (' . $dbg['exception'] . ')' : '.'));
}

selpictLog('ENDE: ' . count($response['files']) . ' Dateien');
if ($debugOn) { $response['debug'] = $dbg; }
echo json_encode($response, SELPICT_JSON_FLAGS);
exit;
