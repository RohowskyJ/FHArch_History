<?php

declare(strict_types=1);

namespace FWHist\Modules\Psa\Lists; 

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/FWAList_php-error.log.txt");

/**
 * Liste "Wappen im Feuerwehrgebrauch" (psw_ff_wappen), verknüpft über fo_fw_id = fw_id.
 * Spaltennamen gegen das tatsächliche Schema (psw_ff_wappen: fo_id, fo_fw_id,
 * fo_ff_wappen, fo_ff_w_sort, fo_ff_w_komm) verifiziert - 29.08.2026.
 */
final class FWAList implements ListInterface
{
    /** Spalten der Liste (zusätzlich action) - nur diese Felder gehen an den Browser */
    private const SHOW_COLS = ["fo_id", "fo_ff_w_komm", "fo_ff_wappen"];

    private static string $logFile = "../../../login/AOrd_Verz/Logging/FWAList_debug.log.txt";
    
    private static array $GrafFiles = array("gif","ico","jpeg","jpg","png","tiff");
    
    public static function getListId(): string
    {
        return 'FW';
    }
    
    /** Aufbereiten der Titel- Zeile */
    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["psw_ff_wappen"]);
        
        $TabTitles = [];
        $altTitel = [];
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
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 8, "hozAlign" => "center", "headerSort" => false, "formatter" => 'html'];
            } elseif ($fldName == 'fo_ff_wappen') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "formatter" => 'html'];
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
    
    /** Auslesen und Aubfbereiten der Daten */
    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        #self::log("selId " . var_export($selId, true));
        #self::log("editProtect " . var_export($editProtect, true));
        
        # self::log("fwId " . var_export($fwId, true));
        $stmt = $pdo->prepare('SELECT * FROM psw_ff_wappen WHERE fo_fw_id = :fo_fw_id ORDER BY fo_ff_w_sort');
        $stmt->execute(['fo_fw_id' => $selId ?? 0]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as &$row) {
            $id = (int) ($row['fo_id'] ?? 0);
            if (!$editProtect) {
                $row['action'] = "<a class='btn action-link' href='FWAEdit.php?ID={$id}&proj=" . rawurlencode($project ?? 'AERM') . "'>Bearbeiten</a>";
            }
            
            #$r = json_encode($row);
            #self::log("row $r" );
        }
        unset($row);
        
        foreach ($rows as &$row) {
            $this->modifyRow($row);
            $row = array_intersect_key($row, array_flip([...self::SHOW_COLS, 'action']));
        }
        unset($row);
        
        return $rows;
    }
    
    /**
     * Modifiziert eine einzelne Zeile vor der Ausgabe
     * @param array $row
     * @param string $tabTyp
     * @return bool
     */
    protected function modifyRow(array &$row ): bool
    {
        #$grafs = json_encode(self::$GrafFiles);
        #self::log("GrafFiles " . $grafs);
        
        $d_path = "../../../login/AOrd_Verz/PSA/AERM/Wappen_FW/";
        
        if ($row['fo_ff_wappen'] != "") {
            $bild1 = htmlspecialchars((string) $row['fo_ff_wappen'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION));
            
            $graffile = false;
            foreach (self::$GrafFiles as $key => $val ) {
                #self::log("$val");
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                #self::log("Bild");
                $row['fo_ff_wappen'] = "<a href='$d_path$bild1' target='_blank'><img src='$d_path$bild1' alt='$bild1' height='100' ></a>";
            } else {
                $row['fo_ff_wappen'] = "<a href='$d_path$bild1' target='_blank'>$bild1</a>";
            }
        }
        
        return true;
    }
    
    /** Error- Logging */
    protected static function log(string $message): void
    {
        $timestamp = date("Y-m-d H:i:s");
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}