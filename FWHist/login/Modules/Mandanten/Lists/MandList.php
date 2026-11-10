<?php

declare(strict_types=1);

namespace FWHist\Modules\Mandanten\Lists;

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;



/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../../login/AOrd_Verz/Logging/MandList_php-error.log.txt");

final class MandList implements ListInterface
{
    /** Spalten der Liste (zusätzlich ei_org_name für den Textfilter und action) - nur diese Felder gehen an den Browser */
    private const SHOW_COLS = ["ei_id", "ei_staat", "ei_bdld", "ei_name", "ei_adresse", "ei_plz", "ei_ort", "ei_internet"];

    private static string $logFile = "../../../login/AOrd_Verz/Logging/MandList_debug.log.txt";

    public static function getListId(): string
    {
        #self::Log("in getlist");
        return 'MA';
    }

    /** Aufbereiten der Titel- Zeile */
    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten

        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["ma_mandanten"]);

        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = ["ei_staat" => "Landes- Name"]; // alternative Titel zu den Feld- Kommentaren

        $altTitel = ['Nachname' =>'Name'];
        /*
         $lTitel = ["Alle" => "Alle Ehrungen ",
         "Bund" => "vom Bund (Staat) ausgegebenen Auszeichnungen ",
         "Bdld" => "vom Bundesland ausgegebenen Auszeichnungen ",
         "Gde"  => "von einer Gemeinde ausgegebenen Auszeichnungen ",
         "Vereine" =>  "von einer Verein ausgegebenen Auszeichnungen ",
         ];
         */

        $showCols = self::SHOW_COLS;

        /** erstellen der Titel Header */
        $colComment = $meta->getCommentsMap();
        $colStyles  = $meta->getStylesMap();
        $colTypes = $meta->getTypesMap();
        $colLength = $meta->getMaxLengthsMap();

        # $TabTitles[] = ["title" => "Details", "field" => "detail", "width" =>  90 , "hozAlign" => "center",  "headerSort" => false ,  "formatter" => "html" ];

        foreach ($showCols as $fldName ) {
            $titel = "";
            if (isset($altTitel[$fldName]) AND $altTitel[$fldName] !=  "" ) {
                $titel = $altTitel[$fldName];
            } elseif (isset($colComment[$fldName]) and $colComment[$fldName] !=  "" ) {
                $titel = $colComment[$fldName];
            } else {
                $titel = ucfirst($fldName);
            }

            if ($fldName == 'ei_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" =>  8 ,  "hozAlign" => "center", "headerSort" => false, "formatter" => 'html' ];
            } else if ($fldName == 'ei_staat' || $fldName == 'ei_bdld'  ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerfilter" => false, "formatter" => 'plaintext' ];
            } else if ($fldName == 'bs_bild_1' || $fldName == 'bs_bild_2') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "formatter" => 'html' ];
            } else {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerFilter" => false, "headerSort" => false, "formatter" => 'plaintext' ];
            }

        }
        $json = json_encode($TabTitles);
        # self::log("Tabtitles $json");

        return $TabTitles;

    }

    /** erstellen der Filterdaten */
    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $states = $pdo->query(
            'SELECT DISTINCT staat.st_name, staat.st_name
             FROM ma_mandanten
             INNER JOIN hi_staaten staat ON staat.st_abkzg = ma_mandanten.ei_staat
             WHERE ma_mandanten.ei_staat <> ""
             ORDER BY staat.st_name ASC'
        )->fetchAll(PDO::FETCH_KEY_PAIR);
        $regions = $pdo->query(
            'SELECT DISTINCT bundesland.bl_name, bundesland.bl_name
             FROM ma_mandanten
             INNER JOIN hi_bundesld bundesland ON bundesland.bl_blabkz = ma_mandanten.ei_bdld
             WHERE ma_mandanten.ei_bdld <> ""
             ORDER BY bundesland.bl_name ASC'
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'selectFilters' => [
                'ei_staat' => [
                    'label' => 'Staat',
                    'options' => $states,
                ],
                'ei_bdld' => [
                    'label' => 'Bundesland',
                    'options' => $regions,
                ],
            ],
            'textFilters' => ['ei_org_name', 'ei_name'],
        ];
    }

    /** Auslesen und Aubfbereiten der Daten */
    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {

        # self::log("project $project");
        $stmt = $pdo->query('SELECT * FROM ma_mandanten ORDER BY ei_id');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $states = $pdo->query('SELECT st_abkzg, st_name FROM hi_staaten')->fetchAll(PDO::FETCH_KEY_PAIR);
        $regions = $pdo->query('SELECT bl_blabkz, bl_name FROM hi_bundesld')->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($rows as &$row) {
            $id = (int) ($row['ei_id'] ?? 0);
            // Action-Link immer; ohne Schreibrecht "Anzeigen"
            $actionLabel = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='MandEdit.php?ID={$id}'>{$actionLabel}</a>";
            if (isset($states[$row['ei_staat']])) {
                $row['ei_staat'] = $states[$row['ei_staat']];
            }
            if (is_null($row['ei_bdld'])) {
                $row['ei_bdld'] = '';
            }
            if (isset($regions[$row['ei_bdld']])) {
                $row['ei_bdld'] = $regions[$row['ei_bdld']];
            }
            
            if (!empty($row['ei_vname'])) {
                $name = $row['ei_name'] ? trim($row['ei_name']) : '';
                $vname = $row['ei_vname'] ? trim($row['ei_vname']) : '';
                $row['ei_name'] = $name . " " . $vname;
            }
            // Nur Listenfelder ausliefern (Telefon, E-Mail, Einverständnisse, Änderungsdaten usw. bleiben auf dem Server)
            $row = array_intersect_key($row, array_flip([...self::SHOW_COLS, 'ei_org_name', 'action']));
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
