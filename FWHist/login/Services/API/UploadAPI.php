<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 1) . '/CommFuncsLib.php';
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use FWHist\Auth\AccessGuard;
use FWHist\Database\DB_GenericLog;

// RBAC: ARC/INV/FZG prüfen den Mandanten 'eigner'; MEDIA prüft den Upload-Ziel-Eigentümer 'urhNr'.
// Unbekannte subMod: verweigert.
$requestedSubMod = (string) ($_POST['subMod'] ?? '');
$isPublicView = ($_SESSION['BS_Prim']['Mod']['smod'] ?? '') === 'index';
$subModKey = strtoupper(trim(explode('|', $requestedSubMod)[0]));
$isMediaUpload = in_array($subModKey, ['MEDIA', 'ADM-MED'], true);
$rawGateEiId = $isMediaUpload ? ($_POST['urhNr'] ?? '') : ($_POST['eigner'] ?? '');
$gateEiId = is_scalar($rawGateEiId)
    && ctype_digit((string) $rawGateEiId)
    && strlen((string) $rawGateEiId) <= 10
        ? (int) $rawGateEiId
        : 0;
$guard = AccessGuard::fromSession(new DB_GenericLog());
if ($isPublicView || !$guard->canUpload($requestedSubMod, $gateEiId)) {
    error_log('UploadAPI: Upload verweigert, subMod=' . $requestedSubMod . ' ziel_ei_id=' . $gateEiId
        . ' be_id=' . (int) ($_SESSION['BS_Prim']['BE']['be_id'] ?? 0));
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Änderungen sind nicht erlaubt.']);
    exit;
}
if (!AccessGuard::checkCsrf($_POST['csrf'] ?? null)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => AccessGuard::CSRF_MESSAGE], JSON_UNESCAPED_UNICODE);
    exit;
}
$subModKey = strtoupper(trim(explode('|', $requestedSubMod)[0]));
$mandantBound = str_starts_with($subModKey, 'ARC') || $subModKey === 'ADM-ARC'
    || str_starts_with($subModKey, 'INV') || str_starts_with($subModKey, 'FZG');

/**
 * UploadAPI.php  (login/Services/API/UploadAPI.php)
 *
 * Bereinigte Zusammenführung von VF_C_MassUp.API.php (aktive Produktiv-Version) und
 * MassUpLibAPI.php (FWHist-Zwischenstand) - Basis-Verhalten bleibt wie in der aktiven
 * App, nur Bugs behoben und auf ConstLib.php-Konstanten umgestellt.
 *
 * Ein Request = eine Datei (passend zu MassUpLibs.js: sequentieller Upload je Datei).
 *
 * Gegenüber der Vorlage behoben:
 *  - $uploadDir: fehlender Slash nach __DIR__
 *  - doppelte/tote $w-Zuweisung ("N-" wird sofort durch "W-" ueberschrieben) - jetzt
 *    abhaengig von $urhEinfgJa, damit der -W-/-N--Marker im Dateinamen stimmt
 *  - config_u.ini-Fallback entfernt (Urheber-Name kommt jetzt vom Autocomplete-Feld im
 *    Frontend, siehe AutoComp_Urheber.js - die ini war nur eine Notloesung)
 *  - Extension-Listen kommen aus ConstLib.php (GrafFiles/AudioFiles/VideoFiles/DocFiles)
 *    statt hartcodierter Arrays, damit sie nicht mehr auseinanderlaufen koennen
 *  - is_file($storePath) vor mkdir() war falsch (prueft auf Datei statt Verzeichnis) -> is_dir()
 *  - Tippfehler $subMd -> $subMod im Debug-Log
 *  - Schriftart-Pfad auf Feuerwehr_Archiv/FWHist/fonts/ umgestellt
 *
 * Bestaetigt (2026-09-12): ALLE neu hochgeladenen Fotos, die der Namensregel
 * Urheber-Datum-W|N-Dateiname folgen, gehen einheitlich nach urhNr/09/06/Datum/ -
 * unabhaengig von aOrd. Der bisherige aOrd-Sonderpfad war nur fuer Altbestand relevant
 * und wird hier fuer NEUE Uploads nicht mehr verwendet (Altbestand bleibt unangetastet
 * an seinem bisherigen Ort auffindbar, wird spaeter separat migriert).
 *
 * Korrektur 2026-09-13: aOrd kommt NICHT aus einer eigenen Multi-Select-Auswahl im
 * Upload-Block (die dort ursprünglich vorgesehene AOrd-Auswahl_$j wurde entfernt - falscher
 * Ort dafür), sondern von der aufrufenden Seite bereits fertig aus $neu['ad_sg']/
 * $neu['ad_subsg'] zusammengesetzt als "sg/subsg" (z.B. "01/02") übergeben - bei Updates aus
 * der DB, bei Neu-Eingaben aus der Multi-Select-Abfrage in ArchEdit_ph0.inc.php. Die Hilfsfunktion
 * archivordnungPfadSegment() bleibt bestehen (idempotent für bereits Slash-getrennte Werte),
 * falls andere Module (PSA/Fallback) noch einen Composite-String im alten Format liefern.
 *
 * Root Cause geklärt (2026-09-13): $_POST['aOrd'] kam beim AJAX-Einzel-Upload leer an, weil
 * das Frontend (UploadUnifiedLib.php) den falschen DOM-Knoten selektierte - siehe dort. Kein
 * Custom-Element-/Form-Association-Problem, fw-multi-select legt ganz normal ein echtes
 * <input type="hidden"> im Light DOM an.
 *
 * Bestätigt 2026-09-13: für alle Nicht-Mandanten-Module (OEF: Buch/Marktplatz/Museen/Presse/
 * Termine) ist bereits ein fixer Zielordner je Untermodul erforderlich (AOrd_Verz/{Feldname})
 * - das leistet die $oefDirs-Zuordnung unten (aus subMod, nicht aus $aOrd) bereits, keine
 * Änderung nötig.
 *
 * Bugfix 2026-09-13: $aufnDat wird jetzt serverseitig auf [A-Za-z0-9_] beschränkt (Whitelist),
 * da es 1:1 Teil des Ziel-Dateinamens/-Pfads wird - ergänzt zur clientseitigen Einschränkung
 * am Formularfeld in UploadUnifiedLib.php.
 *
 * Bugfix 2026-09-13: die alte Regel "kein Wasserzeichen wenn aOrd gesetzt" wurde entfernt.
 * Sie stammte aus der Zeit, in der aOrd nur bei gezielter Dokument-Auswahl pro Slot befüllt
 * war (= Indikator "das ist kein Foto"). Seit aOrd seitenweit aus $neu['ad_sg']/ad_subsg
 * kommt, ist es fast immer gesetzt und hätte das Wasserzeichen faktisch immer deaktiviert,
 * unabhängig vom Radio-Button "Urheber ins Bild einfügen". Einziges verbleibendes
 * Ausschlusskriterium: unbekannter Urheber ($urhNr === '').
 *
 * Umstellung 2026-09-27 (Medien_Verwaltung.md / Pfad-Tabelle.md): Frontend (UploadUnifiedLib.php)
 * baut den Ablagepfad jetzt zentral selbst und schickt ihn als eine Variable $_POST['storPath']
 * mit (z.B. "21/09/06/20260101/" für ein Foto, "21/01/02/" für ein Dokument). Diese Datei
 * verwendet storPath ab sofort als primäre Quelle für ALLE Dateitypen (Foto/Audio/Video/
 * Dokument) - die bisherige, pro Dateityp/Modul verzweigte Pfad-Logik unten bleibt NUR als
 * Fallback bestehen, falls storPath (noch) nicht mitgeschickt wird (z.B. älterer/nicht
 * umgestellter Aufrufer). Sicherheit: resolveStorePath() bereinigt storPath Segment für Segment
 * (nur [A-Za-z0-9_-] je Segment, kein "..") - storPath kommt vom Client und darf NIE
 * ungeprüft in eine Filesystem-Operation.
 * Klarstellung Josef 2026-09-27: die Foto/Audio/Video-Bibliothek (urhNr/09/{05|06|10}/Datum)
 * ist MODULÜBERGREIFEND fix - auch bei Archivalien (ARC) landet ein Foto dort, NICHT im
 * Archivalien-Ordner ({eigner}/{ad_sg}/{ad_subsg}/, dorthin gehören AUSSCHLIESSLICH
 * Dokumente). Deckt sich für Fotos exakt mit der schon bestehenden, 2026-09-12 bestätigten
 * Fallback-Regel unten (handleImageSpecial() ignoriert aOrd bereits vollständig).
 * Ebenfalls von Josef bestätigt: ad_subsg 05=Audio/06=Foto/10=Video (nicht 02/06/10) und
 * OEF-DO-Zielordner "Dokumente" (nicht "Downloads") - der Audio/Video-Fallback unten wurde
 * entsprechend von 02 auf 05 korrigiert.
 *
 * Bugfix 2026-09-29: (1) resizeImage() (FotoFuncs.php) speichert IMMER JPEG und liefert den Pfad mit
 * Endung .jpg zurück - der Zielname (.png) stimmte damit nie mit der tatsächlichen Datei überein.
 * handleImageSpecial() vergibt jetzt von Anfang an die Endung .jpg; die Datei wird nach der
 * Verarbeitung auf Existenz geprüft, Ausnahmen aus der Bildverarbeitung werden abgefangen (sonst
 * kam statt JSON eine leere Antwort). (2) Sammlung (sammlg) wird für die Bibliotheks-Suche in
 * {urh_nr}_dmedien.md_sammlg mitgespeichert. (3) Debug-Log liegt jetzt mit absolutem Pfad in
 * AOrd_Verz/Logging/ (vorher relativ zum Arbeitsverzeichnis).
 *
 * Änderung 2026-10-03: (1) OEF-Bilder: Urheber/Aufnahmedatum optional (Default Urheber 'OEF' /
 * 'Temporär', Datum = heute), Ablage im OEF-Ordner laut storPath, KEIN Eintrag in
 * {urh_nr}_dmedien, Wasserzeichen weiterhin möglich. (2) Kein Hochskalieren mehr - siehe
 * resizeImage() in FotoFuncs.php (reSize = Obergrenze).
 *
 * @author Josef Rohowsky, überarbeitet 2026-10-03
 */

$debug_log = true;
$debug_log_file = dirname(__DIR__, 2) . '/AOrd_Verz/Logging/Upload_debug.log.txt';
@mkdir(dirname($debug_log_file), 0755, true);
if ($debug_log) {
    file_put_contents($debug_log_file, "\n==== API CALL ====\n" . date('Y-m-d H:i:s') . "\nMETHOD: " . @$_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
}

require_once dirname(__DIR__,1) . '/ConstLib.php';   // liefert GrafFiles, AudioFiles, VideoFiles, DocFiles
require_once dirname(__DIR__,1) . '/FotoFuncs.php';
require_once dirname(__DIR__,1) . '/MedienDbLib.php';  // legt {urh_nr}_dmedien-Datensätze an (Medien-Verwaltung)  // resizeImage() / convertImage() (bereinigt, Rotation jetzt Parameter statt global)

ini_set('display_errors', '0');
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__,2) . '/AOrd_Verz/Logging/UploadAPI_php-error.log.txt');

$uploadDir   = dirname(__DIR__,2) . '/AOrd_Verz/VF_Upload/'; // temp. Zielverzeichnis für den Roh-Upload
$aordRoot    = dirname(__DIR__,2) . '/AOrd_Verz/';           // Wurzel aller Ablage-Verzeichnisse (login/AOrd_Verz/)
$maxFileSize = 40 * 1024 * 1024; // 40 MB
$allowedExtensions = array_merge(GrafFiles, AudioFiles, VideoFiles, DocFiles);

/**
 * Zerlegt den Composite-Wert des Archivordnung-Multi-Select in das Pfad-relevante
 * Verzeichnis-Segment.
 *
 * Beispiel: "01.02 04" -> "01/02" (die Ebene "04" ist nur fuer die Tabelle, nicht fuer
 * den Pfad). Ein Wert ohne "." und ohne " " (z.B. "AERM") kommt unveraendert zurueck.
 *
 * @param string $aOrd Roh-Wert aus $_POST['aOrd']
 * @return string Pfad-Segment ohne führenden/folgenden Slash, z.B. "01/02"
 */
function archivordnungPfadSegment(string $aOrd): string
{
    $pfadTeil = explode(' ', trim($aOrd), 2)[0];
    $segments = array_filter(explode('.', $pfadTeil), static fn($s) => $s !== '');
    return implode('/', $segments);
}

/**
 * Löst den vom Frontend zentral gebauten $_POST['storPath'] (z.B. "21/09/06/20260101/")
 * sicher zu einem absoluten Ablagepfad unterhalb von $aordRoot auf.
 *
 * WICHTIG: storPath kommt vom Client (Hidden-Feld + JS-Template, siehe UploadUnifiedLib.php)
 * und darf daher NIE ungeprüft in eine Filesystem-Operation übernommen werden. Jedes
 * "/"-getrennte Segment wird einzeln auf [A-Za-z0-9_-] reduziert; leere Segmente sowie "."
 * und ".." werden verworfen - ein Verzeichnis-Traversal-Versuch (z.B. "../../etc") kann
 * damit nicht aus $aordRoot ausbrechen. Liefert '' wenn nach der Bereinigung nichts übrig bleibt.
 *
 * @param string $aordRoot absoluter Pfad login/AOrd_Verz/ (mit trailing slash)
 * @param string $rawStorPath roher Wert aus $_POST['storPath']
 * @return string absoluter, bereinigter Ablagepfad mit trailing slash, oder ''
 */
function resolveStorePath(string $aordRoot, string $rawStorPath): string
{
    $segments = array_filter(explode('/', $rawStorPath), static fn($s) => $s !== '');
    $clean = [];
    foreach ($segments as $seg) {
        $seg = preg_replace('/[^A-Za-z0-9_-]/', '', $seg);
        if ($seg === '' || $seg === '.' || $seg === '..') {
            continue;
        }
        $clean[] = $seg;
    }
    if (!$clean) {
        return '';
    }
    return $aordRoot . implode('/', $clean) . '/';
}

/**
 * Legt eine hochgeladene Datei aus dem temporären Upload-Verzeichnis (VF_Upload) im Zielverzeichnis
 * ab: Verzeichnis anlegen/prüfen, Datei VERSCHIEBEN (rename; Fallback copy+unlink) und jeden
 * Schritt auf Erfolg prüfen. Die frühere Variante rief mkdir()/copy() ohne Rückgabewert-Prüfung
 * auf: scheiterte eines davon (z.B. fehlende Schreibrechte im Zielverzeichnis), blieb die Datei
 * unbemerkt in VF_Upload liegen, die Antwort meldete aber trotzdem Erfolg mit einem Pfad, unter
 * dem nichts existierte (Bugfix 2026-09-27).
 *
 * @return string|false vollständiger Zielpfad bei Erfolg, sonst false (Fehlertext in $errors)
 */
function legeDateiAb(string $quelle, string $zielDir, string $zielName, array &$errors, string $anzeigeName)
{
    if (!is_dir($zielDir) && !@mkdir($zielDir, 0755, true) && !is_dir($zielDir)) {
        $err = error_get_last()['message'] ?? 'unbekannter Fehler';
        error_log("UploadAPI: mkdir fehlgeschlagen: $zielDir - $err");
        $errors[] = "$anzeigeName: Zielverzeichnis konnte nicht angelegt werden ($zielDir).";
        return false;
    }
    if (!is_writable($zielDir)) {
        error_log("UploadAPI: Zielverzeichnis nicht beschreibbar: $zielDir");
        $errors[] = "$anzeigeName: Zielverzeichnis nicht beschreibbar ($zielDir).";
        return false;
    }
    $ziel = rtrim($zielDir, '/') . '/' . $zielName;
    if (!@rename($quelle, $ziel)) {
        if (!@copy($quelle, $ziel)) {
            $err = error_get_last()['message'] ?? 'unbekannter Fehler';
            error_log("UploadAPI: Ablage fehlgeschlagen: $quelle -> $ziel - $err");
            $errors[] = "$anzeigeName: Datei konnte nicht nach $zielDir abgelegt werden.";
            return false;
        }
        @unlink($quelle);
    }
    return $ziel;
}

/**
 * Bildbearbeitung: Rotation, Resize, Wasserzeichen, Umbenennung nach Urh-Datum-Schema.
 */
function handleImageSpecial($filePath, $urhNr, $urhName, $aufnDat, $rotation = 0, $urhEinfgJa = 'N', $resize = '800', $aOrd = '', $storePathOverride = '', $subMod = '')
{
    global $debug_log, $debug_log_file, $aordRoot;

    // Vorrang hat der vom Frontend zentral gebaute storPath (siehe Docblock oben); nur wenn
    // der (noch) fehlt, gilt die alte fixe Regel "immer 09/06/Datum, unabhängig von aOrd".
    // OEF-Bilder ohne storPath: fixer Ordner je Untermodul (wie bei den OEF-Dokumenten), nicht die Medien-Bibliothek.
    $mod_arr = explode('|', (string) $subMod);
    $oefDirsBild = ['AN' => 'Marktplatz', 'MP' => 'Marktplatz', 'BU' => 'Buch', 'DO' => 'Dokumente', 'BO' => 'Dokumente', 'MUS' => 'Museen', 'PR' => 'Presse', 'TE' => 'Termine'];
    if ($storePathOverride !== '') {
        $storePath = $storePathOverride;
    } elseif (strncasecmp($mod_arr[0], 'OEF', 3) === 0 && isset($oefDirsBild[strtoupper($mod_arr[1] ?? '')])) {
        $storePath = $aordRoot . $oefDirsBild[strtoupper($mod_arr[1])] . '/';
    } else {
        $storePath = $aordRoot . "$urhNr/09/06/$aufnDat/";
    }

    if (!is_dir($storePath)) {
        mkdir($storePath, 0755, true);
    }

    if ($debug_log) {
        file_put_contents($debug_log_file, "handleImageSpecial: filePath=$filePath rotation=$rotation urhEinfgJa=$urhEinfgJa resize=$resize storePath=$storePath urhName=$urhName\n", FILE_APPEND);
    }

    $basename = basename($filePath);
    $fn_arr = explode('-', $basename);
    $fcnt = count($fn_arr);
    $fileName = $fn_arr[$fcnt - 1];

    $watermarkEingefuegt = ($urhEinfgJa === 'J');
    $marker = $watermarkEingefuegt ? 'W' : 'N';

    if ($fcnt < 3) { // noch nicht im Urh-Datum-Marker-Original-Schema -> umbenennen
        $out_name = "$urhNr-$aufnDat-$marker-$fileName";
    } else {
        $out_name = $fileName; // Original-Namensschema bleibt erhalten
    }
    // resizeImage() schreibt IMMER JPEG (imagejpeg) -> Endung von Anfang an .jpg, damit Ziel-Name,
    // Rückgabewert, Datei auf der Platte und der Eintrag in {urh_nr}_dmedien übereinstimmen.
    $out_name   = pathinfo($out_name, PATHINFO_FILENAME) . '.jpg';
    $outputFile = $storePath . $out_name;

    $CR_text = $watermarkEingefuegt ? ('© ' . $urhName) : '';
    $ttf_file = dirname(__DIR__, 3) . '/fonts/arialbd.ttf'; // Feuerwehr_Archiv/FWHist/fonts/

    $res_file = resizeImage($filePath, $resize, $resize, $outputFile, $CR_text, $rotation, $ttf_file, $debug_log);
    if ($res_file == "" && $debug_log) {
        file_put_contents($debug_log_file, "Fehler beim Resizing des Bildes: $filePath\n", FILE_APPEND);
    }
    return $res_file !== '' ? $res_file : $outputFile;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Anfrage.']);
    exit;
}

$response = ['status' => 'ok', 'files' => [], 'errors' => [], 'warnings' => []];

// Metadaten
$rotations  = $_POST['rotation'] ?? '';
$urhNr      = ($_POST['urhNr'] ?? '') === 'undefined' ? '' : ($_POST['urhNr'] ?? '');
$urhName    = $_POST['urhName'] ?? '';
$aufnDat    = preg_replace('/[^A-Za-z0-9_]/', '', $_POST['aufnDat'] ?? '');
$urhEinfgJa = $_POST['urhEinfgJa'] ?? 'N';
$reSize     = $_POST['reSize'] ?? '800';
$aOrd       = $_POST['aOrd'] ?? '';
$eigner     = $_POST['eigner'] ?? '0';
$subMod     = $_POST['subMod'] ?? '';
// Pfad-relevante Client-Werte bereinigen (gehen unmittelbar in Dateipfade/-namen ein)
$urhNr  = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $urhNr);
$eigner = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $eigner);
// Beschreibung aus dem Datensatz-Formular (ad_beschreibg / in_beschreibg) -> md_beschreibg
$beschreibg = trim((string) ($_POST['beschreibg'] ?? ''));
// Sammlung (ad_sammlg / in_sammlg) -> md_sammlg (varchar(40)); macht neue Fotos in der Bibliotheks-Suche auffindbar
$sammlg     = mb_substr(trim((string) ($_POST['sammlg'] ?? '')), 0, 40);
// Zentral vom Frontend gebauter Ablagepfad (siehe Docblock/resolveStorePath oben); '' wenn
// nicht mitgeschickt oder nach Bereinigung leer -> dann greift der alte Fallback unten.
$storPathResolved = resolveStorePath($aordRoot, trim((string) ($_POST['storPath'] ?? '')));
// erstes Segment des Ablagepfads (bei mandantenbezogenen Modulen der Mandant)
$storSeg1 = $storPathResolved !== '' ? (explode('/', substr($storPathResolved, strlen($aordRoot)))[0] ?? '') : '';

$urhEinfgJa = in_array(strtoupper(trim((string) $urhEinfgJa)), ['J', 'JA', '1', 'TRUE'], true) ? 'J' : 'N';
$urhEinfgWunsch = $urhEinfgJa; // Wunsch aus dem Formular, bevor die Regel "unbekannter Urheber" greift
if ($urhNr === '') {
    $urhEinfgJa = 'N'; // kein Wasserzeichen bei unbekanntem Urheber
}

if ($debug_log) {
    file_put_contents($debug_log_file, "urhEinfgJa=$urhEinfgJa aufnDat=$aufnDat urhNr=$urhNr urhName=$urhName aOrd=$aOrd eigner=$eigner reSize=$reSize subMod=$subMod storPath=$storPathResolved\n", FILE_APPEND);
}

if (!isset($_FILES['file'])) {
    echo json_encode(['status' => 'error', 'message' => 'Keine Datei(en) empfangen.']);
    exit;
}

$fileCount = is_array($_FILES['file']['name']) ? count($_FILES['file']['name']) : 1;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

for ($i = 0; $i < $fileCount; $i++) {
    $name  = is_array($_FILES['file']['name']) ? $_FILES['file']['name'][$i] : $_FILES['file']['name'];
    $tmp   = is_array($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'][$i] : $_FILES['file']['tmp_name'];
    $error = is_array($_FILES['file']['error']) ? $_FILES['file']['error'][$i] : $_FILES['file']['error'];
    $size  = is_array($_FILES['file']['size']) ? $_FILES['file']['size'][$i] : $_FILES['file']['size'];
    $ext   = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $rotation = (int) (is_array($rotations) ? ($rotations[$i] ?? 0) : $rotations);

    $trans  = ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ß' => 'ss', ' - ' => '_', '-' => '_', ' ' => '_'];
    $rename = strtr($name, $trans);

    if ($error !== UPLOAD_ERR_OK) {
        $response['errors'][] = "$name: Upload-Fehler ($error)";
        continue;
    }
    if (!in_array($ext, $allowedExtensions)) {
        $response['errors'][] = "$name: Ungültige Dateiendung ($ext)";
        continue;
    }
    if ($size > $maxFileSize) {
        $response['errors'][] = "$name: Datei zu groß";
        continue;
    }
    // Dokumente mandantenbezogener Module dürfen nur in den Ordner des berechtigten Mandanten (nie per storPath fremd)
    if ($mandantBound && !in_array($ext, GrafFiles) && $storSeg1 !== '' && $storSeg1 !== (string) $gateEiId) {
        $response['errors'][] = "$name: Zielverzeichnis gehört nicht zum Mandanten.";
        continue;
    }

    // OEF-Bilder (Öffentlichkeitsarbeit, 2026-10-03): Urheber und Aufnahmedatum sind optional.
    // Die Bilder kommen von wechselnden Quellen und werden nur an dieser einen Stelle verwendet,
    // daher Standardwerte statt eines eigenen Mandanten: Urheber 'OEF' / Name 'Temporär',
    // Datum = Tagesdatum (YYYYmmdd). Ein im Formular gewählter Urheber bzw. ein eingegebenes
    // Datum hat Vorrang. Ablage laut storPath (docPathTemplate des OEF-Eintrags), KEINE
    // Registrierung in {urh_nr}_dmedien (siehe weiter unten). Wasserzeichen bleibt möglich.
    $oefBild   = (strncasecmp(explode('|', $subMod)[0], 'OEF', 3) === 0) && in_array($ext, GrafFiles); // "OEF" oder "OEF-MP"
    $fUrhNr    = $urhNr;
    $fUrhName  = $urhName;
    $fAufnDat  = $aufnDat;
    if ($oefBild) {
        if ($fUrhNr === '')   { $fUrhNr = 'OEF'; }
        if ($fUrhName === '') { $fUrhName = 'Temporär'; }
        if ($fAufnDat === '') { $fAufnDat = date('Ymd'); }
    }
    $fUrhEinfg = ($fUrhNr === '') ? 'N' : $urhEinfgWunsch; // kein Wasserzeichen bei unbekanntem Urheber

    $targetPath = $uploadDir . basename($rename);
    if (!move_uploaded_file($tmp, $targetPath)) {
        $response['errors'][] = "$name: Fehler beim Speichern.";
        continue;
    }

    if (in_array($ext, GrafFiles)) {
        try {
            $targetPath = handleImageSpecial($targetPath, $fUrhNr, $fUrhName, $fAufnDat, $rotation, $fUrhEinfg, $reSize, $aOrd, $storPathResolved, $subMod);
        } catch (Throwable $e) {
            error_log('UploadAPI: Bildverarbeitung fehlgeschlagen: ' . $e->getMessage());
            if ($debug_log) { file_put_contents($debug_log_file, 'Bildverarbeitung FEHLER: ' . $e->getMessage() . "\n", FILE_APPEND); }
            $response['errors'][] = "$name: Bildverarbeitung fehlgeschlagen (" . $e->getMessage() . ').';
            continue;
        }
        if (!is_file($targetPath)) {
            $response['errors'][] = "$name: Bilddatei wurde nicht erzeugt ($targetPath).";
            continue;
        }
    } elseif ($storPathResolved !== '') {
        // Audio/Video/Dokumente: zentral vom Frontend gebauter Pfad hat Vorrang (Medien_Verwaltung.md-
        // Wunsch) - ersetzt die darunter liegende alte, pro Modul/Dateityp verzweigte Logik.
        $abgelegt = legeDateiAb($targetPath, $storPathResolved, basename($rename), $response['errors'], $name);
        if ($abgelegt === false) { continue; }
        $targetPath = $abgelegt;
    } elseif (in_array($ext, AudioFiles)) {
        // Fallback, falls kein storPath mitkam (05 = Audio, von Josef 2026-09-27 bestätigt).
        $abgelegt = legeDateiAb($targetPath, $aordRoot . "$urhNr/09/05/", basename($rename), $response['errors'], $name);
        if ($abgelegt === false) { continue; }
        $targetPath = $abgelegt;
    } elseif (in_array($ext, VideoFiles)) {
        // Fallback, falls kein storPath mitkam.
        $abgelegt = legeDateiAb($targetPath, $aordRoot . "$urhNr/09/10/", basename($rename), $response['errors'], $name);
        if ($abgelegt === false) { continue; }
        $targetPath = $abgelegt;
    } else {
        // Fallback, falls kein storPath mitkam: Dokumente - Ziel abhängig vom Modul
        // (subMod = "MODUL|UNTERMODUL").
        $mod_arr = explode('|', $subMod);
        $storePath = '';
        switch (strncasecmp($mod_arr[0], 'OEF', 3) === 0 ? 'OEF' : $mod_arr[0]) { // "OEF" und "OEF-MP"
            case 'OEF':
                $sub = $mod_arr[1] ?? '';
                $oefDirs = ['AN' => 'Marktplatz', 'BU' => 'Buch', 'DO' => 'Dokumente', 'MUS' => 'Museen', 'PR' => 'Presse', 'TE' => 'Termine'];
                if (isset($oefDirs[$sub])) {
                    $storePath = $aordRoot . $oefDirs[$sub] . '/';
                }
                break;
            case 'PSA':
                $storePath = $aordRoot . "$eigner/" . archivordnungPfadSegment($aOrd) . "/";
                break;
            case 'ARC':
                if ($eigner != '' && $aOrd != '') {
                    $storePath = $aordRoot . "$eigner/" . archivordnungPfadSegment($aOrd) . "/";
                } else {
                    $response['errors'][] = "$name: Verzeichnis nach Archivordnung nicht angegeben.";
                }
                break;
            case 'INV':
                $storePath = $aordRoot . "$eigner/INV/";
                break;
            case 'Media':
                $storePath = $aordRoot . "$urhNr/09/";
                break;
            default:
                // generischer Fallback für nicht gelistete Module: Dokument nach Archivordnung
                if ($eigner != '' && $aOrd != '') {
                    $storePath = $aordRoot . "$eigner/" . archivordnungPfadSegment($aOrd) . "/";
                } elseif ($aOrd != '') {
                    $storePath = $aordRoot . archivordnungPfadSegment($aOrd) . "/";
                } else {
                    $response['errors'][] = "$name: Verzeichnis nach Archivordnung nicht angegeben.";
                }
        }
        if ($storePath === '') {
            continue; // Fehler wurde oben schon eingetragen
        }
        $abgelegt = legeDateiAb($targetPath, $storePath, basename($rename), $response['errors'], $name);
        if ($abgelegt === false) { continue; }
        $targetPath = $abgelegt;
    }

    // Medien-Verwaltung: Foto/Audio/Video bekommen Verzeichnis- und Detail-Datensatz in {urh_nr}_dmedien
    // (Ausnahme: Upload aus dem Medien-Modul selbst - dessen Formular legt die Datensätze an).
    // Ein Fehler hier macht den Upload nicht ungültig, er wird nur als Warnung gemeldet.
    $medium = in_array($ext, GrafFiles) ? 'Foto' : (in_array($ext, AudioFiles) ? 'Audio' : (in_array($ext, VideoFiles) ? 'Video' : ''));
    if ($medium !== '' && !$oefBild && !medienModulIstMedien($subMod)) {
        $warnung = medienRegistrierenSicher([
            'urhNr'      => $urhNr,
            'urhName'    => $urhName,
            'aufnDat'    => $aufnDat,
            'dsn'        => basename($targetPath),
            'beschreibg' => $beschreibg,
            'sammlg'     => $sammlg,
            'medium'     => $medium,
        ]);
        if ($warnung !== null) {
            $response['warnings'][] = basename($targetPath) . ': ' . $warnung;
        }
    }

    $response['files'][] = [
        'name'     => $rename,
        'path'     => $targetPath,
        'filename' => basename($targetPath),
        'rotation' => $rotation,
        'size'     => $size
    ];
}

if ($debug_log) {
    file_put_contents($debug_log_file, json_encode($response) . "\n", FILE_APPEND);
}

echo json_encode($response);
exit;
