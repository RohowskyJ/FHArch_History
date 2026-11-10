<?php

declare(strict_types=1);

namespace FWHist\Modules\Psa\Lists;

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/AWAList_php-error.log.txt");

/**
 * Liste "Ärmel- und T-Shirt-Abzeichen" (psw_aermel_abz), verknüpft über fo_fw_id = fw_id.
 *
 * Ersetzt das alte, eigenständige System (AWA_Liste_API.php / AWA_ListRepository.php /
 * AWA_ListTableConfig.php, aufgerufen über die alte AermAbzList_inc.php) durch die
 * generische ListInterface/ListHostAPI-Infrastruktur - fachliche Logik (Bild-Handling,
 * Abzeichen-Typ-Text, Sortierung) 1:1 aus AWA_ListRepository/-TableConfig übernommen.
 *
 * ACHTUNG (Josef): Bild-Pfad $imgBasePath unten via $basePath aufgebaut (analog zu den
 * anderen Modul-Assets) - bitte gegen den bisherigen relativen Pfad
 * "../../../login/AOrd_Verz/PSA/AERM/Aermel_Abz/" aus AWA_ListRepository prüfen, falls
 * sich die tatsächliche Verzeichnisstruktur auf dem Server unterscheidet.
 */
final class AWAList implements ListInterface
{
    /** Spalten der Liste (zusätzlich fo_id und action) - nur diese Felder gehen an den Browser */
    private const SHOW_COLS = ["fo_ff_abz_typ", "fo_ff_abzeich"];

    private static string $logFile = "../../../login/AOrd_Verz/Logging/AWAList_debug.log.txt";

    /** Grafik-Dateiendungen, die als <img> statt als reiner Link dargestellt werden */
    private static array $grafFiles = ["gif", "ico", "jpeg", "jpg", "png", "tiff"];

    /** Klartext je Ärmelabzeichen-Typ-Kürzel */
    private static array $aermelabzText = [
        'TE' => 'Spezielle Beschreibung notwendig',
        'BR' => 'Ärmelabzeichen für die Braune Uniform',
        'BB' => 'Ärmelabzeichen für die Braune oder Blaue Uniform',
        'BG' => 'Ärmelabzeichen für die Braune oder Grüne Uniform',
        'BL' => 'Ärmelabzeichen für die Blaue Uniform',
        'HE' => 'Ärmelabzeichen für das Hemd',
        'PO' => 'Abzeichen für das Poloshirt, Fleecejacke, ...',
        'GR' => 'Ärmelabzeichen für die Grüne Uniorm',
        'FR' => 'Zugehörigkeitsabzeichen auf Freizeitkleidung (ev. Verein)',
        'XX' => 'Noch nicht näher bestimmte Verwendung',
    ];

    public static function getListId(): string
    {
        return 'AWA';
    }

    /** Aufbereiten der Titel- Zeile */
    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["psw_aermel_abz"]);

        $TabTitles = [];
        $altTitel = ["fo_ff_abzeichen" => "Bild"];
        $showCols = self::SHOW_COLS;

        $colComment = $meta->getCommentsMap();

        foreach ($showCols as $fldName) {
            $titel = "";
            if (isset($altTitel[$fldName]) AND $altTitel[$fldName] !=  "") {
                $titel = $altTitel[$fldName];
            } elseif (isset($colComment[$fldName]) and $colComment[$fldName] !=  "") {
                $titel = $colComment[$fldName];
            } else {
                $titel = ucfirst($fldName);
            }

            if ($fldName == 'fo_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 50, "hozAlign" => "center", "headerSort" => false, "formatter" => 'plaintext'];
            } elseif ($fldName == 'fo_fw_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 80, "hozAlign" => "center", "headerSort" => false, "formatter" => 'plaintext'];
            } elseif ($fldName == 'fo_ff_abz_typ') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 400, "headerSort" => false, "headerFilter" => false, "formatter" => 'textarea', "variableHeight" => true];
            } elseif ($fldName == 'fo_ff_abzeich') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "headerFilter" => false, "formatter" => 'html'];
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
            'selectFilters' => [],
            'textFilters' => [],
        ];
    }

    /** Auslesen und Aufbereiten der Daten */
    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        # self::log("fwId " . var_export($fwId, true));
        $stmt = $pdo->prepare('SELECT * FROM psw_aermel_abz WHERE fo_fw_id = :fo_fw_id ORDER BY fo_ff_a_sort');
        $stmt->execute(['fo_fw_id' => $selId ?? 0]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $this->modifyRow($row, $project, $editProtect);
        }
        
        unset($row);

        return $rows;
    }

    /** Aufbereiten einer einzelnen Zeile (Edit-Link, Typ-Text, Bild-Darstellung) */
    private function modifyRow(array &$row, ?string $project, ?bool $editProtect): void
    {
        $id = (int) ($row['fo_id'] ?? 0);
        $fwId = (int) ($row['fo_fw_id'] ?? 0);
        if (!$editProtect) {
            $row['action'] = "<a class='btn action-link' href='AermAbzEdit.php?ID={$id}&fo_fw_id={$fwId}&proj=" . rawurlencode($project ?? 'AERM') . "'>Bearbeiten</a>";
        }

        if (($row['fo_ff_abz_typ'] ?? '') === '') {
            $row['fo_ff_abz_typ'] = self::$aermelabzText[$row['fo_ff_a_typ_a'] ?? ''] ?? '';
        }

        // TODO Josef: Pfad ggf. an tatsächliche Verzeichnisstruktur anpassen (siehe Klassenkommentar).
        $imgBasePath = "../../../login/AOrd_Verz/PSA/AERM/Aermel_Abz/";

        if (($row['fo_ff_abzeich'] ?? '') !== '') {
            $bild1 = htmlspecialchars((string) $row['fo_ff_abzeich'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION));

            if (in_array($ext, self::$grafFiles, true)) {
                $row['fo_ff_abzeich'] = "<a href='{$imgBasePath}{$bild1}' target='_blank'><img src='{$imgBasePath}{$bild1}' alt='{$bild1}' height='80'></a>";
            } else {
                $row['fo_ff_abzeich'] = "<a href='{$imgBasePath}{$bild1}' target='_blank'>{$bild1}</a>";
            }
        }

        // Nur Listenfelder ausliefern (fo_ff_a_typ_a, Sortierung, Änderungsdaten usw. bleiben auf dem Server)
        $row = array_intersect_key($row, array_flip([...self::SHOW_COLS, 'fo_id', 'action']));
    }

    /** Error- Logging */
    protected static function log(string $message): void
    {
        $timestamp = date("Y-m-d H:i:s");
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}
