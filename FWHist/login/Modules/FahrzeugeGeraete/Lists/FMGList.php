<?php

declare(strict_types=1);

namespace FWHist\Modules\FahrzeugeGeraete\Lists;

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;
use InvalidArgumentException;

const GrafFiles = array("gif","ico","jpeg","jpg","png","tiff");

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/FMG_List_php-error.log.txt");

final class FMGList implements ListInterface
{
    private static string $logFile = "../../../login/AOrd_Verz/Logging/FMG_List_debug.log.txt";
    
    public static function getListId(): string
    {
        #self::Log("in getlist");
        return 'FMG';
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
        $colsByTable = $meta->getColumnsForTables([$selId."_fzm_geraet"]);
        
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
        
        $showCols = ["mg_id", "mg_sammlg", "mg_type", "mg_baujahr", "mg_herst", "mg_bezeich", "mg_foto_1",  ];
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
            
            if ($fldName == 'mg_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 70 ,  "hozAlign" => "center", "headerSort" => true, "formatter" => 'html' ];
            } else if ($fldName == 'mg_type' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" => 120 , "headerfilter" => false, "formatter" => 'plaintext' ];
            } elseif ($fldName == 'mg_baujahr') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" => 120 ,"headerSort" => true, "formatter" => 'html' ];
            } elseif ($fldName == 'mg_sammlg') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" => 120 ,"headerSort" => true, "formatter" => 'html' ];
            } elseif ($fldName == 'mg_bezeichn') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "formatter" => 'html' ];
            } else if ($fldName == 'mg_foto_1' || $fldName == 'mg_beszeichng') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerSort" => false, "formatter" => 'html' ];
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
        // mg_sg/mg_subsg/mg_lcsg/mg_lcssg/mg_lcssg_s0/mg_lcssg_s1 werden nicht mehr
        // über einen eigenen Select-Filter angeboten, sondern ausschließlich über
        // die Kaskade im <fw-multi-select> (Archivordnung) gefiltert -- siehe
        // 'multiSelectFilter' in der lists[]-Konfiguration von ArchivList.php.
        return [
            'selectFilters' => [
                /*
                 'mg_sg' => [
                 'label' => 'ArchivOrdnung',
                 'options' => ['01'=>'Lokaler FW. Dienst', '07'=>'	Feuerwehr- Fahrzeuge, Häuser, Geräte', '10'=>'Literatur', ''=>'',]
                 ],
        
                 'mg_sammlg' => [
                 'label' => 'Sammlung',
                 'options' => ['MA_F'=>'Motor- Fahrzeuge', 'MA_G'=>'Motor- Geräte', 'MU_F'=>'Muskel-Fahrzeuge', 'MU_G'=>'Muskel-Geräte'],
                 ],
                 */
            ],
            'textFilters' => ['mg_sammlg', 'mg_taktbez', 'mg_baujahr', 'mg_allg_beschr','mg_keywords', ],
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
        $tableName = "{$selId}_fzm_geraet";
        
        # self::log("project $project");
        $stmt = $pdo->query("SELECT * FROM {$tableName} ORDER BY mg_id");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        
        
        foreach ($rows as &$row) {
            $mg_id = (int) ($row['mg_id'] ?? 0);
            $row['action'] = "<a class='btn action-link' href='FAFEdit.php?mg_id={$mg_id}&ei_id={$selId}'>Edit</a>";
            
            $mg_type = $row['mg_type'] ? $row['mg_type'] : NULL;

            if ($mg_type) {
                $mg_type = $row['mg_type'];
                $row['mg_type'] = $Arc_Type[$mg_type];
            }
            
            // Ergänzung des Namens zur Abkürzung mg_sammlg
            if (!empty($row['mg_sammlg'])) {
                $saName = $this->findSaName($row['mg_sammlg'], $rowSammlg);
                if ($saName !== null) {
                    $row['mg_sammlg'] .= "<br>" . htmlspecialchars($saName);
                }
            }
  
            // Hersteller ergänzen
            if (!empty($row['mg_herst']) && isset($herst_arr[$row['mg_herst']])) {
                $row['mg_herst'] = htmlspecialchars($herst_arr[$row['mg_herst']]);
            }
            
            if (!empty($row['mg_baujahr'])) {
                $row['fm_baujahr'] = "Bj: " . $row['mg_baujahr'];
            }
            
            if (!empty($row['mg_indienst'])) {
                $row['mg_baujahr'] = "<br>In: " . $row['mg_indienst'];
            }
            
            if (!empty($row['mg_ausdienst'])) {
                $row['mg_baujahr'] = "<br>Au: " . $row['mg_ausdienst'];
            }
            
            /** Bilder / Pdfs Anzeigen */
            
            $d_path = "../../../login/AOrd_Verz/"; 
            if ($row['mg_foto_1'] != "") {
                $bild1 = $row['mg_foto_1'];
                $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION));
                
                $graffile = false;
                foreach (GrafFiles as $key => $val ) {
                    if ($val == $ext ) {
                        $graffile = true;
                        break;
                    }
                }

                if ($graffile) {
                    // Bild analysieren für _W_ _N_
                    $picArr = explode('-', $bild1);
                    $pAcnt = count($picArr);
                    
                    if ($pAcnt >= 3) {
                        $pPath = $picArr[0] . "/09/06/" . $picArr[1] . "/";
                        $dPathPict = $d_path . $pPath;
                    } else {
                        $dPathPict = $d_path . "$selId/MuG/"; //dieser Pfad gilt nicht für Biler mit _W_ oder _N_ bzw [urhId]-[AufnDat]-[-W|N-}OrigBildName
                    }
                    $row['mg_foto_1'] = "<a href='$dPathPict$bild1' target='_blank'><img src='$dPathPict$bild1' alt='$bild1' height='200' ></a>";
                } else {
                    $dPathNoPict = $d_path . "$selId/$aord/";
                    $row['mg_foto_1'] = "<a href='$dPathNoPict$bild1' target='_blank'>$bild1</a>";
                }
            }
            #  unset($row['fw_ab_name'], $row['fw_typ'], $row['fw_kommentar'], $row['fw_auszeich'], $row['fw_aermelw'], $row['fw_changed_at']);
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
