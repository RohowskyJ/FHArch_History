<?php

declare(strict_types=1);

namespace FWHist\Modules\Psa\Lists;

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;

const GrafFiles = array("gif","ico","jpeg","jpg","png","tiff");

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/ORTsList_php-error.log.txt");

final class OrtsList implements ListInterface
{
    /** Spalten der Liste (zusätzlich detail und action) - nur diese Felder gehen an den Browser */
    private const SHOW_COLS = ["fw_id", "st_abkzg", "bl_blabkz", "fw_bz_name", "fw_gd_name", "fw_fw_name"];

    private static string $logFile = "../../../login/AOrd_Verz/Logging/ORTsList_debug.log.txt";

    public static function getListId(): string
    {
        return 'ORT';
    }

    /** Aufbereiten der Titel- Zeile */
    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten

        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["pso_ort_ref"]);

        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = ["st_abkzg" => "Landes- Name"]; // alternative Titel zu den Feld- Kommentaren

        #$altTitel = [];
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

        $TabTitles[] = ["title" => "Details", "field" => "detail", "width" =>  90 , "hozAlign" => "center",  "headerSort" => false ,  "formatter" => "html" ];

        foreach ($showCols as $fldName ) {
            $titel = "";
            if (isset($altTitel[$fldName]) AND $altTitel[$fldName] !=  "" ) {
                $titel = $altTitel[$fldName];
            } elseif (isset($colComment[$fldName]) and $colComment[$fldName] !=  "" ) {
                $titel = $colComment[$fldName];
            } else {
                $titel = ucfirst($fldName);
            }

            if ($fldName == 'fw_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" =>  8 ,  "hozAlign" => "center", "headerSort" => false, "formatter" => 'html' ];
            } else if ($fldName == 'st_abkzg' || $fldName == 'bl_blabkz'  || $fldName == 'fw_bz_name'|| $fldName == 'fw_gd_name') {
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
             FROM pso_ort_ref ort
             INNER JOIN hi_staaten staat ON staat.st_abkzg = ort.st_abkzg
             WHERE ort.st_abkzg <> ""
             ORDER BY staat.st_name ASC'
        )->fetchAll(PDO::FETCH_KEY_PAIR);
        $regions = $pdo->query(
            'SELECT DISTINCT bundesland.bl_name, bundesland.bl_name
             FROM pso_ort_ref ort
             INNER JOIN fv_bundesld bundesland ON bundesland.bl_blabkz = ort.bl_blabkz
             WHERE ort.bl_blabkz <> ""
             ORDER BY bundesland.bl_name ASC'
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'selectFilters' => [
                'st_abkzg' => [
                    'label' => 'Staat',
                    'options' => $states,
                ],
                'bl_blabkz' => [
                    'label' => 'Bundesland',
                    'options' => $regions,
                ],
            ],
            'textFilters' => ['fw_bz_name', 'fw_gd_name', 'fw_fw_name'],
        ];
    }

    /** Auslesen und Aubfbereiten der Daten */
    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {

        # self::log("project $project");
        $stmt = $pdo->query('SELECT * FROM pso_ort_ref ORDER BY fw_id');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $states = $pdo->query('SELECT st_abkzg, st_name FROM hi_staaten')->fetchAll(PDO::FETCH_KEY_PAIR);
        $regions = $pdo->query('SELECT bl_blabkz, bl_name FROM fv_bundesld')->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($rows as &$row) {
            $id = (int) ($row['fw_id'] ?? 0);
            // Action-Link immer (Ort-Formular mit den Sub-Listen); ohne Schreibrecht "Anzeigen" (Formular schreibgeschützt)
            $actionLabel = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='OrtsEdit.php?ID={$id}&proj=" . rawurlencode($project ?? 'AERM') . "'>{$actionLabel}</a>";
            if (isset($states[$row['st_abkzg']])) {
                $row['st_abkzg'] = $states[$row['st_abkzg']];
            }
            if (isset($regions[$row['bl_blabkz']])) {
                $row['bl_blabkz'] = $regions[$row['bl_blabkz']];
            }
            $detailLinks = [];
            if ($project === 'AERM' && ($row['fw_aermelw'] ?? '') !== '') {
                $detailLinks[] = "<a class='btn action-link' href='AermDetail.php?ID={$id}&amp;proj=AERM'>Ärmelabzeichen</a>";
            }
            if (
                ($project === 'AERM' && ($row['fw_auszeich'] ?? '') === 'J')
                || ($project === 'EHRZ' && ($row['fw_auszeich'] ?? '') !== '')
            ) {
                $detailLinks[] = "<a class='btn action-link' href='EhrgDetail.php?ID={$id}&amp;proj=EHRZ'>Ehrenzeichen</a>";
            }
            if ($detailLinks !== []) {
                $row['detail'] = implode(' ', $detailLinks);
            }

            // Nur Listenfelder ausliefern (die oben ausgewerteten Felder fw_aermelw/fw_auszeich u. a. bleiben auf dem Server)
            $row = array_intersect_key($row, array_flip([...self::SHOW_COLS, 'detail', 'action']));
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
