<?php

declare(strict_types=1);

namespace FWHist\Modules\Psa\Lists;

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/OWAList_php-error.log.txt");

final class OWAList implements ListInterface
{
    /** Spalten der Liste (zusätzlich action) - nur diese Felder gehen an den Browser */
    private const SHOW_COLS = ["fo_id", "fo_gde_w_komment", "fo_gde_wappen"];

    private static string $logFile = "../../../login/AOrd_Verz/Logging/OWAList_debug.log.txt";
    
    private static array $GrafFiles = array("gif","ico","jpeg","jpg","png","tiff");

    public static function getListId(): string
    {
        return 'OW';
    }

    /** Aufbereiten der Titel- Zeile */
    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        
        #self::log("selId " . var_export($selId, true));
        #self::log("editProtect " . var_export($editProtect, true));
        
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten
        
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["psw_ort_wappen"]);
        
        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = []; // alternative Titel zu den Feld- Kommentaren
        
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
        
        foreach ($showCols as $fldName ) {
            $titel = "";
            if (isset($altTitel[$fldName]) AND $altTitel[$fldName] !=  "" ) {
                $titel = $altTitel[$fldName];
            } elseif (isset($colComment[$fldName]) and $colComment[$fldName] !=  "" ) {
                $titel = $colComment[$fldName];
            } else {
                $titel = ucfirst($fldName);
            }
            
            if ($fldName == 'fo_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" =>  8 ,  "hozAlign" => "center", "headerSort" => false, "formatter" => 'html' ];
            } else if ($fldName == 'st_abkzg' || $fldName == 'bl_blabkz'  || $fldName == 'fw_bz_name'|| $fldName == 'fw_gd_name') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerfilter" => false, "formatter" => 'plaintext' ];
            } else if ($fldName == 'fo_gde_wappen' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "formatter" => 'html' ];
            } else {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerFilter" => false, "headerSort" => false, "formatter" => 'plaintext' ];
            }
            
        }
        #$json = json_encode($TabTitles);
        # self::log("Tabtitles $json");
        
        return $TabTitles;
 
    }

    /** erstellen der Filterdaten */
    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        
        #self::log("selId " . var_export($selId, true));
        #self::log("editProtect " . var_export($editProtect, true));
        
        /*
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
        */
        return ['selectFilters[]', 'textFilters[]'];
    }

    /** Auslesen und Aubfbereiten der Daten */
    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        #self::log("selId " . var_export($selId, true));
        #self::log("editProtect " . var_export($editProtect, true));
        
        # self::log("fwId " . var_export($fwId, true));
        $stmt = $pdo->prepare('SELECT * FROM psw_ort_wappen WHERE fo_fw_id = :fo_fw_id ORDER BY fo_gde_w_sort');
        $stmt->execute(['fo_fw_id' => $selId ?? 0]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

       # $states = $pdo->query('SELECT st_abkzg, st_name FROM hi_staaten')->fetchAll(PDO::FETCH_KEY_PAIR);
        # $regions = $pdo->query('SELECT bl_blabkz, bl_name FROM fv_bundesld')->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($rows as &$row) {
            $id = (int) ($row['fo_id'] ?? 0);
            if (!$editProtect) {
                $row['action'] = "<a class='btn action-link' href='OWAEdit.php?ID={$id}'>Bearbeiten</a>";
            }
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
        
        $d_path = "../../../login/AOrd_Verz/PSA/AERM/Wappen_Ort/";
        
        if ($row['fo_gde_wappen'] != "") {
            $bild1 = htmlspecialchars((string) $row['fo_gde_wappen'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
                $row['fo_gde_wappen'] = "<a href='$d_path$bild1' target='_blank'><img src='$d_path$bild1' alt='$bild1' height='100' ></a>";
            } else {
                $row['fo_gde_wappen'] = "<a href='$d_path$bild1' target='_blank'>$bild1</a>";
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