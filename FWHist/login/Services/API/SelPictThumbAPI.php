<?php
/**
 * SelPictThumbAPI.php  (login/Services/API/SelPictThumbAPI.php)
 *
 * Liefert ein verkleinertes Vorschaubild (Thumbnail, JPEG) einer Datei der Foto-Lib für die
 * Bibliotheks-Auswahl (UploadUnifiedLib.php). Vorteile gegenüber <img src="Originalpfad">:
 *  - unabhängig vom Web-Pfad (der Pfad wird serverseitig aus urhNr/aufnDat/dsn gebildet),
 *  - deutlich kleinere Datenmenge (bis zu 500 Bilder je Suche),
 *  - Thumbnails werden gecacht: AOrd_Verz/Thumbs/{urh_nr}/{md5}_{breite}.jpg
 *    (wird neu erzeugt, wenn das Original neuer ist).
 *
 * GET-Parameter:
 *   urhNr    Urheber-Nr. (nur Ziffern)
 *   aufnDat  Aufnahmedatum = Verzeichnis (A-Z a-z 0-9 _), darf leer sein
 *   dsn      Dateiname (nur Name, kein Pfad; Endung muss in GrafFiles enthalten sein)
 *   w        größte Kantenlänge in Pixel (60-800, Default 160)
 *
 * Quelle: AOrd_Verz/{urh_nr}/09/06/{aufn_datum}/{dsn}
 * Fehler: HTTP 400/404/415/500 mit kurzem Text (das Frontend zeigt dann einen Platzhalter).
 * Fehlerlog: AOrd_Verz/Logging/SelPictThumbAPI_php-error.log.txt
 */

ini_set('display_errors', '1');
ini_set('log_errors', 1);
$logDir = dirname(__DIR__, 2) . '/AOrd_Verz/Logging';
@mkdir($logDir, 0755, true);
ini_set('error_log', $logDir . '/SelPictThumbAPI_php-error.log.txt');

require_once dirname(__DIR__, 1) . '/ConstLib.php';      // GrafFiles

function thumbAbbruch(int $code, string $text): void
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $text;
    exit;
}

function thumbAusgabe(string $datei): void
{
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . filesize($datei));
    header('Cache-Control: public, max-age=86400');
    readfile($datei);
    exit;
}

$urhNr   = (string) ($_GET['urhNr'] ?? '');
$aufnDat = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_GET['aufnDat'] ?? ''));
$dsn     = (string) ($_GET['dsn'] ?? '');
$w       = max(60, min(800, (int) ($_GET['w'] ?? 160)));

if ($urhNr === '' || !ctype_digit($urhNr) || strlen($urhNr) > 10) {
    thumbAbbruch(400, 'Ungültige Urheber-Nr.');
}
if ($dsn === '' || $dsn !== basename(str_replace('\\', '/', $dsn)) || strpos($dsn, "\0") !== false) {
    thumbAbbruch(400, 'Ungültiger Dateiname.');
}
if (!in_array(strtolower(pathinfo($dsn, PATHINFO_EXTENSION)), GrafFiles, true)) {
    thumbAbbruch(415, 'Kein Bildformat.');
}
if (!function_exists('imagecreatefromstring')) {
    thumbAbbruch(500, 'PHP-Erweiterung GD fehlt.');
}

// Quelldatei - muss innerhalb von AOrd_Verz liegen
$aordReal = realpath(dirname(__DIR__, 2) . '/AOrd_Verz');
if ($aordReal === false) {
    thumbAbbruch(500, 'AOrd_Verz nicht gefunden.');
}
$aordReal = str_replace('\\', '/', $aordReal);
$quelle   = realpath("$aordReal/$urhNr/09/06/" . ($aufnDat !== '' ? "$aufnDat/" : '') . $dsn);
if ($quelle === false || !is_file($quelle) || strpos(str_replace('\\', '/', $quelle), $aordReal . '/') !== 0) {
    thumbAbbruch(404, 'Datei nicht gefunden.');
}

// Cache
$cacheDir  = "$aordReal/Thumbs/$urhNr";
$cacheDatei = $cacheDir . '/' . md5($aufnDat . '/' . $dsn) . '_' . $w . '.jpg';
if (is_file($cacheDatei) && filemtime($cacheDatei) >= filemtime($quelle)) {
    thumbAusgabe($cacheDatei);
}

try {
    $info = @getimagesize($quelle);
    if (!$info) {
        thumbAbbruch(415, 'Bild nicht lesbar.');
    }
    if ($info[0] * $info[1] > 40000000) { // ~40 Megapixel: Speicher schützen
        thumbAbbruch(415, 'Bild zu groß für Vorschau.');
    }
    $img = @imagecreatefromstring((string) file_get_contents($quelle));
    if (!$img) {
        thumbAbbruch(415, 'Bild konnte nicht geladen werden.');
    }
    $ow = imagesx($img);
    $oh = imagesy($img);
    $skala = min(1, $w / max($ow, $oh));
    $nw = max(1, (int) round($ow * $skala));
    $nh = max(1, (int) round($oh * $skala));

    $thumb = imagecreatetruecolor($nw, $nh);
    imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255)); // Transparenz (PNG/GIF) -> weiß
    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $nw, $nh, $ow, $oh);

    // in den Cache schreiben (atomar); nicht beschreibbar -> direkt ausgeben
    @mkdir($cacheDir, 0755, true);
    $tmp = $cacheDatei . '.' . getmypid() . '.tmp';
    if (is_dir($cacheDir) && is_writable($cacheDir) && imagejpeg($thumb, $tmp, 82) && @rename($tmp, $cacheDatei)) {
        thumbAusgabe($cacheDatei);
    }
    @unlink($tmp);
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=3600');
    imagejpeg($thumb, null, 82);
    exit;
} catch (Throwable $e) {
    error_log('SelPictThumbAPI: ' . $e->getMessage() . " ($quelle)");
    thumbAbbruch(500, 'Fehler bei der Vorschau-Erzeugung.');
}
