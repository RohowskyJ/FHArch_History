<?php
/**
 * FotoFuncs.php  (login/Services/FotoFuncs.php)
 *
 * Bildbearbeitungs-Funktionen für den Foto-Upload: Resize, Rotation, Urheber-Wasserzeichen,
 * Format-Konvertierung. Verschoben/bereinigt aus VF_Foto_Funcs.lib.php.
 *
 * Änderungen gegenüber der Vorlage:
 *  - $rotation und $watermark sind jetzt explizite Parameter von resizeImage() statt
 *    globaler Variablen (die globale Variante wurde vom Aufrufer nie gesetzt - die
 *    Rotation griff dadurch faktisch nie)
 *  - $ttf_file bleibt Parameter mit Default, zeigt jetzt auf Feuerwehr_Archiv/FWHist/fonts/
 *  - Debug-Ausgaben vereinheitlicht über ein $debug_log-Flag je Aufruf statt globaler Variable
 *
 *  - 2026-10-03: resizeImage() vergrößert kleine Bilder nicht mehr (maxWidth/maxHeight = Obergrenze)
 *
 * @author Josef Rohowsky, überarbeitet 2026-10-03
 */

/**
 * Konvertiert jpg/png/gif verlustfrei in jpg (ohne Resize).
 *
 * @param string $inputFile
 * @param string $outputFile
 * @param bool   $debug_log
 */
function convertImage($inputFile, $outputFile, $debug_log = false)
{
    $debug_file = "Foto_up_debug.log";
    if ($debug_log) {
        file_put_contents($debug_file, "FotoFuncs convertImage: $inputFile -> $outputFile\n", FILE_APPEND);
    }

    [$width, $height, $type] = getimagesize($inputFile);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($inputFile);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($inputFile);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($inputFile);
            break;
        default:
            throw new Exception('Unsupported image type');
    }

    $newImage = imagecreatetruecolor($width, $height);
    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $width, $height, $width, $height);
    imagejpeg($newImage, $outputFile);

    imagedestroy($source);
    imagedestroy($newImage);

    if ($debug_log) {
        file_put_contents($debug_file, "FotoFuncs convertImage: fertig\n", FILE_APPEND);
    }
}

/**
 * Rotiert (optional), resized und versieht ein Bild mit Urheber-Wasserzeichen.
 * Löscht die Eingabedatei nach erfolgreicher Verarbeitung.
 *
 * @param string $file          Pfad der hochgeladenen Quelldatei
 * @param string|int $maxWidth
 * @param string|int $maxHeight
 * @param string $outputPath    Ziel-Vollpfad (inkl. Dateiname)
 * @param string $copyrightText Wasserzeichen-Text, leer = kein Wasserzeichen
 * @param int    $rotation      Rotation in Grad (0/90/180/270/negative Werte erlaubt), Default 0
 * @param string $ttf_file      Pfad zur TTF-Schrift für das Wasserzeichen
 * @param bool   $debug_log
 * @return string Vollpfad der fertigen Ausgabedatei, oder "" bei Fehler
 */
function resizeImage($file, $maxWidth = '800', $maxHeight = '800', $outputPath = '', $copyrightText = '', $rotation = 0, $ttf_file = '', $debug_log = false)
{
    if ($ttf_file === '') {
        $ttf_file = dirname(__DIR__,3) . '/fonts/arialbd.ttf'; // Feuerwehr_Archiv/FWHist/fonts/
    }

    if ((int) $rotation !== 0) {
        $img = @imagecreatefromstring(file_get_contents($file));
        if (!$img) {
            if ($debug_log) {
                file_put_contents('Foto_Func_debug.log', "resizeImage: Bild laden fehlgeschlagen ($file)\n", FILE_APPEND);
            }
            return "";
        }
        $rot_img = imagerotate($img, (int) $rotation, 0);

        switch (exif_imagetype($file)) {
            case IMAGETYPE_JPEG:
                imagejpeg($rot_img, $file, 100);
                break;
            case IMAGETYPE_PNG:
                imagepng($rot_img, $file);
                break;
            case IMAGETYPE_GIF:
                imagegif($rot_img, $file);
                break;
        }
        # imagedestroy($img);
        # imagedestroy($rot_img);
        unset($img);
        unset($rot_img);
    }

    [$width, $height, $type] = getimagesize($file);

    // Nie vergrößern (Änderung 2026-10-03): passt das Bild bereits in die Box maxWidth x maxHeight,
    // bleiben die Originalmaße erhalten (Hochskalieren kostet nur Qualität). Größere Bilder werden
    // wie bisher proportional auf die Box verkleinert. Die Prüfung erfolgt NACH der Rotation,
    // da $width/$height hier bereits die gedrehten Maße sind.
    if ($width <= (int) $maxWidth && $height <= (int) $maxHeight) {
        $maxWidth = $width;
        $maxHeight = $height;
    } else {
        $ratio = $width / $height;
        if ($maxWidth / $maxHeight > $ratio) {
            $maxWidth = $maxHeight * $ratio;
        } else {
            $maxHeight = $maxWidth / $ratio;
        }
    }
    $maxWidth = intval($maxWidth);
    $maxHeight = intval($maxHeight);

    $newImage = imagecreatetruecolor($maxWidth, $maxHeight);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($file);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($file);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($file);
            break;
        default:
            throw new Exception('Unsupported image type');
    }

    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $maxWidth, $maxHeight, $width, $height);

    if ($copyrightText !== "") {
        $textColorWeiss = imagecolorallocate($newImage, 255, 255, 255);
        $textColorSchwarz = imagecolorallocate($newImage, 0, 0, 0);
        $fontSize = 18;

        $textBox = imagettfbbox($fontSize, 0, $ttf_file, $copyrightText);
        $textHeight = $textBox[1] - $textBox[7];

        $x = 10;
        $y = intval($maxHeight - $textHeight - 1);

        // Schwarzer Schatten + weißer Text darüber für Lesbarkeit auf jedem Hintergrund
        imagettftext($newImage, $fontSize, 0, $x, $y, $textColorWeiss, $ttf_file, $copyrightText);
        imagettftext($newImage, $fontSize, 0, $x - 3, $y - 3, $textColorSchwarz, $ttf_file, $copyrightText);
    }

    imagejpeg($newImage, $outputPath);

    unset($source);
    unset($newImage);

    if (file_exists($file)) {
        unlink($file); // Eingabedatei (temp. Upload) löschen
    }

    if ($debug_log) {
        file_put_contents('Foto_Func_debug.log', "resizeImage: fertig -> $outputPath\n", FILE_APPEND);
    }

    $p_arr = pathinfo($outputPath, PATHINFO_ALL);
    return $p_arr['dirname'] . "/" . $p_arr['filename'] . ".jpg";
}
