<?php

declare(strict_types=1);

namespace FWHist\Modules\Mitglieder\Lists;

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/NMList_php-error.log.txt");

/**
 * NMList - "Neue Mitglieder": Anmeldungen, die per E-Mail bestätigt sind
 * (mi_neu_chkd = 'J'), aber noch nicht vom Admin aktiviert wurden
 * (mi_neu_aktiv = 'N'). Sobald MitglAktivate.php eine Anmeldung aktiviert
 * und mi_neu_aktiv = 'J' setzt, verschwindet der Datensatz automatisch aus
 * dieser Liste.
 *
 * list_ID: NM
 */
final class NMList implements ListInterface
{
    private static string $logFile = "../../../login/AOrd_Verz/Logging/NMList_debug.log.txt";

    public static function getListId(): string
    {
        return 'NM';
    }

    /** Aufbereiten der Titel- Zeile */
    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['mi_anmeldung']);
        $colComment = $meta->getCommentsMap();

        // Details- Spalte (wie in OrtsList.php) wird hier nicht benötigt
        $showCols = ['mi_neu_nr', 'mi_name', 'mi_vname', 'mi_anschr', 'mi_plz', 'mi_ort', 'mi_tel_handy', 'mi_email'];

        $TabTitles = [];
        foreach ($showCols as $fldName) {
            $titel = (isset($colComment[$fldName]) && $colComment[$fldName] !== '')
                ? $colComment[$fldName]
                : ucfirst($fldName);

            if ($fldName === 'mi_neu_nr') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 90, "hozAlign" => "center", "headerFilter" => false, "formatter" => 'plaintext'];
            } else {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerFilter" => false, "headerSort" => false, "formatter" => 'plaintext'];
            }
        }

        return $TabTitles;
    }

    /** erstellen der Filterdaten */
    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        return [
            'textFilters' => ['mi_name', 'mi_vname', 'mi_ort'],
        ];
    }

    /** Auslesen und Aufbereiten der Daten */
    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $stmt = $pdo->query(
            "SELECT mi_neu_nr, mi_name, mi_vname, mi_anschr, mi_plz, mi_ort, mi_tel_handy, mi_email
             FROM mi_anmeldung
             WHERE mi_neu_chkd = 'J' AND (mi_neu_aktiv = 'N' OR mi_neu_aktiv = '' )
             ORDER BY mi_neu_nr ASC"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $nr = (int)($row['mi_neu_nr'] ?? 0);
            $row['action'] = "<a class='btn action-link' href='MitglAktivate.php?mi_neu_nr={$nr}'>Aktivieren</a>";
        }
        unset($row);

        return $rows;
    }

    /** Error- Logging */
    protected static function log(string $message): void
    {
        $timestamp = date("Y-m-d H:i:s");
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}
