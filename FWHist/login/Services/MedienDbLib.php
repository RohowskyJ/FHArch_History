<?php

use FWHist\Database\DB_GenericLog;

/**
 * MedienDbLib.php  (login/Services/MedienDbLib.php)
 *
 * Legt beim Upload von Foto/Audio/Video die Datensätze der Medien-Verwaltung an
 * (Medien_Verwaltung.md): Tabelle {urh_nr}_dmedien (falls fehlend), Verzeichnis-Datensatz
 * (md_dsn_1 = '0_Verz', einer je Aufnahmedatum) und Detail-Datensatz (einer je Datei).
 * Reihenfolge: ERST der Verzeichnis-Datensatz (falls für md_aufn_datum noch keiner existiert),
 * DANN der Detail-Datensatz - beides in einer Transaktion.
 *
 * Aufgerufen aus UploadAPI.php nach jeder erfolgreich abgelegten Medien-Datei. Ein Fehler hier
 * darf den Upload NICHT rückgängig machen (die Datei liegt bereits in der Foto-Lib) - er wird
 * geloggt und als Warnung an das Frontend gemeldet (medienRegistrierenSicher()).
 *
 * Änderungen 2026-09-29:
 *  - medienBenutzerId() war in dieser Datei nicht definiert (Call to undefined function wird vom
 *    catch in medienRegistrierenSicher() verschluckt -> es wurde nie ein Datensatz angelegt, falls
 *    die Funktion auch sonst nirgends existiert): Fallback-Definition, liefert null, falls sie nicht
 *    schon anderswo (z.B. CommFuncsLib.php) definiert ist.
 *  - medienModulIstMedien() war doppelt definiert (harmlos, aber überflüssig) - jetzt nur einmal.
 *  - medienGetPdo(): getInstance() ODER new DB_GenericLog() (je nachdem, was die Klasse anbietet).
 *  - Debug-Log (MEDIEN_DEBUG) nach AOrd_Verz/Logging/Medien_debug.log.txt; bei aktivem Debug
 *    enthält die Warnung an das Frontend die echte Fehlermeldung.
 *  - md_aufn_datum ist varchar(15): zu lange Werte werden mit klarer Meldung abgelehnt statt mit
 *    einem DB-Fehler im Strict-Mode.
 *  - md_sammlg (varchar(40)) wird beim Detail-Datensatz mitgespeichert, damit neue Fotos in der
 *    Bibliotheks-Suche (SelPictLibAPI.php) über die Sammlung auffindbar sind.
 */

/** Debug-Log ein/aus (im Produktivbetrieb auf false setzen). */
if (!defined('MEDIEN_DEBUG')) {
    define('MEDIEN_DEBUG', true);
}

if (!function_exists('medienLog')) {
    function medienLog(string $msg): void
    {
        if (!MEDIEN_DEBUG) {
            return;
        }
        $dir = dirname(__DIR__) . '/AOrd_Verz/Logging';
        @mkdir($dir, 0755, true);
        @file_put_contents($dir . '/Medien_debug.log.txt', date('Y-m-d H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
    }
}

if (!function_exists('medienGetPdo')) {
    function medienGetPdo(): ?PDO
    {
        try {
            require_once __DIR__ . '/../Database/DB_GenericLog.php';
            $inst = method_exists(DB_GenericLog::class, 'getInstance')
                ? DB_GenericLog::getInstance()
                : new DB_GenericLog();
            return $inst->getPDO();
        } catch (Throwable $e) {
            error_log('medienGetPdo: ' . $e->getMessage());
            medienLog('medienGetPdo FEHLER: ' . $e->getMessage());
            return null;
        }
    }
}

/** Ist der Upload aus dem Medien-Modul selbst? Dann legt dessen Formular die Datensätze an. */
if (!function_exists('medienModulIstMedien')) {
    function medienModulIstMedien(string $subMod): bool
    {
        $mod = strtoupper(str_replace(' ', '', trim($subMod)));
        return $mod === 'MEDIA' || str_starts_with($mod, 'MEDIA|');
    }
}

/**
 * Benutzer-ID für md_changed_id (Spalte ist NULL-fähig). Nur Fallback: ist die Funktion schon
 * anderswo definiert (z.B. CommFuncsLib.php), gilt diese. Hier bei Bedarf die Session-Quelle eintragen.
 */
if (!function_exists('medienBenutzerId')) {
    function medienBenutzerId(): ?int
    {
        return null;
    }
}

/** Tabellenname `{urh_nr}_dmedien` - urh_nr wird strikt auf Ziffern geprüft (SQL-Injection-Schutz). */
function medienTabelle(string $urhNr): string
{
    if ($urhNr === '' || !ctype_digit($urhNr) || strlen($urhNr) > 10) {
        throw new InvalidArgumentException("Ungültige Urheber-Nr.: '$urhNr'");
    }
    return '`' . $urhNr . '_dmedien`';
}

/** Legt die Tabelle {urh_nr}_dmedien an, falls sie noch nicht existiert (Struktur laut Medien_Verwaltung.md). */
function medienTabelleAnlegen(PDO $pdo, string $urhNr): void
{
    $t = medienTabelle($urhNr);
    $pdo->exec("CREATE TABLE IF NOT EXISTS $t (
      `md_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
      `ei_id` int(11) DEFAULT NULL COMMENT 'Urheber-Nr.',
      `md_Urheber` varchar(50) NOT NULL COMMENT 'Urheber- Name',
      `md_dsn_1` varchar(255) DEFAULT NULL COMMENT 'Dateiname',
      `md_aufn_datum` varchar(15) DEFAULT NULL COMMENT 'Aufnahme Datum = Verzeichnis',
      `md_beschreibg` varchar(1024) DEFAULT NULL COMMENT 'Beschreibung',
      `md_namen` varchar(1024) DEFAULT NULL COMMENT 'Foto Name',
      `md_sammlg` varchar(40) DEFAULT NULL COMMENT 'Sammlung',
      `md_feuerwehr` varchar(40) DEFAULT NULL COMMENT 'Eigner Name Fahrzeug',
      `md_fw_id` varchar(10) DEFAULT NULL COMMENT 'Eigener- Nr. Fahrzeug',
      `md_suchbegr` varchar(1024) DEFAULT NULL COMMENT 'Suchbegriffe',
      `md_media` set('Audio','Foto','Video') DEFAULT NULL COMMENT 'Medium',
      `md_Med_anz` int(11) DEFAULT NULL COMMENT 'Anzahl der Eintr.',
      `md_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
      `md_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
      PRIMARY KEY (`md_id`),
      UNIQUE KEY `uq_aufn_dsn` (`md_aufn_datum`,`md_dsn_1`),
      FULLTEXT KEY `md_sammlg` (`md_sammlg`),
      FULLTEXT KEY `md_fw_id` (`md_fw_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Digitale- Medien- Archiv'");
}

/**
 * Verzeichnis- und Detail-Datensatz für eine hochgeladene Medien-Datei anlegen/aktualisieren.
 *
 * @param array $d urhNr, urhName, aufnDat, dsn (Dateiname wie abgelegt), beschreibg, sammlg (optional),
 *                 medium ('Foto'|'Audio'|'Video'), userId (?int)
 * @throws Throwable bei DB-/Eingabefehlern (Aufrufer: medienRegistrierenSicher)
 */
function medienRegistrieren(PDO $pdo, array $d): void
{
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $t = medienTabelle($d['urhNr']);
    if (!in_array($d['medium'], ['Foto', 'Audio', 'Video'], true)) {
        throw new InvalidArgumentException('Ungültiges Medium: ' . $d['medium']);
    }
    if (mb_strlen((string) $d['aufnDat']) > 15) {
        throw new InvalidArgumentException('Aufnahmedatum zu lang (max. 15 Zeichen): ' . $d['aufnDat']);
    }
    if (mb_strlen((string) $d['dsn']) > 255) {
        throw new InvalidArgumentException('Dateiname zu lang (max. 255 Zeichen).');
    }
    $urhName = mb_substr((string) ($d['urhName'] ?? ''), 0, 50);
    $beschr  = mb_substr((string) ($d['beschreibg'] ?? ''), 0, 1024);
    $beschr  = $beschr === '' ? null : $beschr;
    $sammlg  = mb_substr(trim((string) ($d['sammlg'] ?? '')), 0, 40);
    $sammlg  = $sammlg === '' ? null : $sammlg;
    $uid     = $d['userId'] ?? null;

    medienTabelleAnlegen($pdo, $d['urhNr']); // CREATE TABLE = impliziter Commit -> vor der Transaktion
    medienLog("Tabelle $t bereit");

    $pdo->beginTransaction();
    try {
        // --- 1. Verzeichnis-Datensatz (einer je Aufnahmedatum) - MUSS zuerst existieren ---
        $sel = $pdo->prepare("SELECT md_id FROM $t WHERE md_dsn_1 = '0_Verz' AND md_aufn_datum = ? LIMIT 1");
        $sel->execute([$d['aufnDat']]);
        $verzId = $sel->fetchColumn();
        medienLog('Verzeichnis-Datensatz vorhanden: ' . var_export($verzId, true));
        if ($verzId === false) {
            try {
                $ins = $pdo->prepare("INSERT INTO $t (ei_id, md_Urheber, md_dsn_1, md_aufn_datum, md_media, md_Med_anz, md_changed_id)
                                      VALUES (?, ?, '0_Verz', ?, ?, 0, ?)");
                $ins->execute([(int) $d['urhNr'], $urhName, $d['aufnDat'], $d['medium'], $uid]);
                $verzId = $pdo->lastInsertId();
                medienLog("Verzeichnis-Datensatz angelegt: md_id=$verzId aufn={$d['aufnDat']}");
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') { throw $e; } // parallel angelegt -> nochmals lesen
                $sel->execute([$d['aufnDat']]);
                $verzId = $sel->fetchColumn();
                medienLog('Verzeichnis-Datensatz parallel angelegt: md_id=' . var_export($verzId, true));
            }
        }
        if ($verzId === false || $verzId === null || (int) $verzId === 0) {
            throw new RuntimeException('Verzeichnis-Datensatz konnte nicht angelegt/gefunden werden.');
        }
        // Medium im Verzeichnis-Datensatz ergänzen, falls dort noch nicht enthalten
        $upd = $pdo->prepare("UPDATE $t SET md_media = IF(md_media IS NULL OR md_media = '', ?, CONCAT(md_media, ',', ?))
                              WHERE md_id = ? AND (md_media IS NULL OR NOT FIND_IN_SET(?, md_media))");
        $upd->execute([$d['medium'], $d['medium'], $verzId, $d['medium']]);

        // --- 2. Detail-Datensatz (einer je Datei) ---
        $selD = $pdo->prepare("SELECT md_id FROM $t WHERE md_dsn_1 = ? AND md_aufn_datum = ? LIMIT 1");
        $selD->execute([$d['dsn'], $d['aufnDat']]);
        $detId = $selD->fetchColumn();
        if ($detId !== false) {
            // erneuter Upload derselben Datei: Metadaten aktualisieren, Zähler unverändert
            $pdo->prepare("UPDATE $t SET md_Urheber = ?, md_beschreibg = ?, md_sammlg = COALESCE(?, md_sammlg), md_media = ?, md_changed_id = ? WHERE md_id = ?")
                ->execute([$urhName, $beschr, $sammlg, $d['medium'], $uid, $detId]);
            medienLog("Detail-Datensatz aktualisiert: md_id=$detId dsn={$d['dsn']}");
        } else {
            $pdo->prepare("INSERT INTO $t (ei_id, md_Urheber, md_dsn_1, md_aufn_datum, md_beschreibg, md_sammlg, md_media, md_changed_id)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([(int) $d['urhNr'], $urhName, $d['dsn'], $d['aufnDat'], $beschr, $sammlg, $d['medium'], $uid]);
            medienLog('Detail-Datensatz angelegt: md_id=' . $pdo->lastInsertId() . " dsn={$d['dsn']}");
            $pdo->prepare("UPDATE $t SET md_Med_anz = COALESCE(md_Med_anz, 0) + 1 WHERE md_id = ?")
                ->execute([$verzId]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $e;
    }
}

/**
 * Wrapper für UploadAPI.php: fängt alle Fehler ab (Upload bleibt gültig) und liefert eine
 * Warnung als Text, oder null bei Erfolg.
 */
function medienRegistrierenSicher(array $d): ?string
{
    try {
        medienLog('START ' . json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        if (($d['urhNr'] ?? '') === '' || ($d['aufnDat'] ?? '') === '') {
            medienLog('ABBRUCH: Urheber-Nr. oder Aufnahmedatum leer');
            return 'Kein Medien-Datensatz angelegt: Urheber-Nr. oder Aufnahmedatum fehlt.';
        }
        $pdo = medienGetPdo();
        if (!$pdo) {
            error_log('MedienDbLib: keine DB-Verbindung (medienGetPdo() anpassen)');
            return 'Kein Medien-Datensatz angelegt: keine Datenbankverbindung.';
        }
        $d['userId'] = medienBenutzerId();
        medienRegistrieren($pdo, $d);
        medienLog('OK ' . ($d['dsn'] ?? ''));
        return null;
    } catch (Throwable $e) {
        $msg = get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine();
        error_log('MedienDbLib: ' . $msg);
        medienLog('FEHLER ' . $msg);
        return 'Datei abgelegt, aber Medien-Datensatz konnte nicht angelegt werden' . (MEDIEN_DEBUG ? " ($msg)" : '.');
    }
}
