<?php

declare(strict_types=1);

namespace FWHist\Modules\Mitglieder\Lists;

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/MMList_php-error.log.txt");

/**
 * MMList - E-Mail an alle Mitglieder ohne die E-Mail Adresse zu kennen
 *
 * list_ID: MM
 */
final class MMList implements ListInterface
{
    private static string $logFile = "../../../login/AOrd_Verz/Logging/MMList_debug.log.txt";

    public static function getListId(): string
    {
        return 'MM';
    }

    /** Aufbereiten der Titel- Zeile */
    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['mi_mitglieder']);
        $colComment = $meta->getCommentsMap();

        // Details- Spalte (wie in OrtsList.php) wird hier nicht benötigt
        $showCols = [ 'mi_name'];

        $TabTitles = [];
        foreach ($showCols as $fldName) {
            $titel = (isset($colComment[$fldName]) && $colComment[$fldName] !== '')
                ? $colComment[$fldName]
                : ucfirst($fldName);

            if ($fldName === 'mi_id') {
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
            'textFilters' => ['mi_name'],
        ];
    }

    /** Auslesen und Aufbereiten der Daten */
    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $stmt = $pdo->query(
            "SELECT mi_name, mi_vname, mi_org_typ, mi_org_name, mi_email, mi_ort, mi_tel_handy, mi_email
             FROM mi_mitglieder
             WHERE mi_austrdat IS NULL AND mi_sterbdat IS NULL AND mi_email LIKE '%@%'
             ORDER BY mi_name, mi_vname ASC"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
         
            $row['action'] = "<a class='btn action-link' href='MitglMEdit.php?mi_email=" . $row['mi_email'] . "'>Mail an</a>";
            
            $name = trim($row['mi_name']);
            $vname = trim($row['mi_vname']);
            
            $orgname = "";
            if ($row['mi_org_typ'] != "") {
                if ($row['mi_org_typ'] != "Privat") {
                    $orgname = trim($row['mi_org_typ']) . " " .trim($row['mi_org_name']);
                }
            }
            $row['mi_name'] = "$name $vname, $orgname";
        }

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
