<?php

declare(strict_types=1);

namespace FWHist\Modules\FahrzeugeGeraete\Lists;

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;
use InvalidArgumentException;

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/FAE_List_php-error.log.txt");

final class FAEList implements ListInterface
{
    private const GRAF_FILES = ["gif", "ico", "jpeg", "jpg", "png", "tiff"];

    private static string $logFile = "../../../login/AOrd_Verz/Logging/FAE_List_debug.log.txt";
    
    public static function getListId(): string
    {
        #self::Log("in getlist");
        return 'FAE';
    }
    
    /** Aufbereiten der Titel- Zeile */
    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        
        // Prüfe, ob $selId gesetzt und nicht leer ist
        if (empty($selId)) {
            throw new InvalidArgumentException('Parameter selId darf nicht leer sein.');
        }
        
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten
        
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables([$selId."_fza_eigner"]);
        
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
        
        $showCols = ["fz_eign_id", "fz_id", "fz_docbez", "fz_zuldaten", "fz_zul_dat", "fz_zul_end_dat"  ];
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
            
            if ($fldName == 'fz_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 90 ,  "hozAlign" => "center", "headerSort" => true, "formatter" => 'html' ];
                
            } elseif ($fldName == 'fz_baujahr') {
                    $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 120 ,  "hozAlign" => "center", "headerSort" => true, "formatter" => 'html' ];
            } else if ($fldName == 'fz_sammlg' || $fldName == 'fz_taktbez' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" => 120 , "headerfilter" => false, "formatter" => 'html' ];
            } else if ( $fldName == 'fz_herstell_fg') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" => 180 , "headerfilter" => false, "formatter" => 'textarea' ];
            } elseif ($fldName == 'fz_allg_beschr') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 260 ,"headerSort" => false, "formatter" => 'textarea' ];
            } else if ($fldName == 'fz_bild_1' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerSort" => false, "formatter" => 'html' ]; // "width" => 250 ,
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
        /*
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
         */
        // ad_sg/ad_subsg/ad_lcsg/ad_lcssg/ad_lcssg_s0/ad_lcssg_s1 werden nicht mehr
        // über einen eigenen Select-Filter angeboten, sondern ausschließlich über
        // die Kaskade im <fw-multi-select> (Archivordnung) gefiltert -- siehe
        // 'multiSelectFilter' in der lists[]-Konfiguration von ArchivList.php.
        return [
            'selectFilters' => [
                /*
                 'ad_sg' => [
                 'label' => 'ArchivOrdnung',
                 'options' => ['01'=>'Lokaler FW. Dienst', '07'=>'	Feuerwehr- Fahrzeuge, Häuser, Geräte', '10'=>'Literatur', ''=>'',]
                 ],
        
                 'ad_sammlg' => [
                 'label' => 'Sammlung',
                 'options' => ['MA_F'=>'Motor- Fahrzeuge', 'MA_G'=>'Motor- Geräte', 'MU_F'=>'Muskel-Fahrzeuge', 'MU_G'=>'Muskel-Geräte'],
                 ],
                 */
            ],
            'textFilters' => ['fz_sammlg', 'fz_taktbez', 'fz_baujahr',],
        ];
    }
    
    /** Auslesen und Aubfbereiten der Daten */
    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        
        // Prüfe, ob $selId gesetzt und nicht leer ist
        if (empty($selId)) {
            throw new InvalidArgumentException('Parameter selId darf nicht leer sein.');
        }

        // Baue den Tabellennamen dynamisch zusammen
        $tableName = "{$selId}_fza_eigner";
        
        // Wandelt $project in Integer um (Sicherheit)
        $projectInt = (int)$project;
        
        // Bereite das Statement mit WHERE-Klausel vor
        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE fz_id = :project ORDER BY fz_eign_id");
        
        // Binde den Integer-Parameter
        $stmt->bindValue(':project', $projectInt, PDO::PARAM_INT);
        
        // Führe das Statement aus
        $stmt->execute();
        
        // Hole alle Ergebnisse
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        /*
        $rowSammlg = $pdo->query('SELECT sa_grup, sa_name, sa_sammlg FROM hi_sammlung')->fetchAll(PDO::FETCH_ASSOC);
       
        // Beispiel: Hersteller- und Aufbauer-Daten einlesen (analog zu deinem Beispiel)
        $herst_arr = [];
        $aufb_arr = [];
        $sql_a = "SELECT * FROM hi_firmen ORDER BY fi_name ASC";
        $res_a = $pdo->query($sql_a);
        while ($row_a = $res_a->fetch(PDO::FETCH_OBJ)) {
            if ($row_a->fi_funkt == 'F') {
                $herst_arr[$row_a->fi_abk] = $row_a->fi_name . ", " . $row_a->fi_ort;
            } else {
                $aufb_arr[$row_a->fi_abk] = $row_a->fi_name . ", " . $row_a->fi_ort;
            }
        }
        
        // Taktische Bezeichnungen einlesen
        $taktb_arr = [];
        $sql_t = "SELECT * FROM hi_abk ORDER BY ab_abk ASC";
        $res_t = $pdo->query($sql_t);
        while ($row_t = $res_t->fetch(PDO::FETCH_OBJ)) {
            $taktb_arr[$row_t->ab_abk] = $row_t->ab_bezeichn;
        }
         */
       # $message = json_encode($herst_arr);
       # self::log($message);
        
        foreach ($rows as &$row) {
            $fz_id = (int) ($row['fz_id'] ?? 0);
            $fz_eign_id = (int)($row['fz_eign_id'] ?? 0);
            $actionLabel = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='FaEEdit.php?fz_id={$fz_id}&ei_id={$selId}&fz_eign_id={$fz_eign_id}'>{$actionLabel}</a>";
        }
        unset($row);
        
        return $rows;
    }
    
    /**
     * Hilfsfunktion: Suche sa_name passend zur Abkürzung fz_sammlg
     */
    private function findSaName(string $abbr, array $sammlungen): ?string
    {
        // Exakte Suche
        foreach ($sammlungen as $sammlung) {
            if ($sammlung['sa_sammlg'] === $abbr) {
                return $sammlung['sa_name'];
            }
        }
        
        // Schrittweise Kürzung der Abkürzung (Bindestrich-getrennt)
        $parts = explode('-', $abbr);
        while (count($parts) > 1) {
            array_pop($parts);
            $shorterAbbr = implode('-', $parts);
            
            foreach ($sammlungen as $sammlung) {
                if ($sammlung['sa_sammlg'] === $shorterAbbr) {
                    return $sammlung['sa_name'];
                }
            }
        }
        
        // Kein Treffer
        return null;
    }
    
    /** Error- Logging */
    protected static function log(string $message): void
    {
        $timestamp = date("Y-m-d H:i:s");
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}
