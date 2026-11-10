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
ini_set('error_log', "../../../login/AOrd_Verz/Logging/FAG_List_php-error.log.txt");

final class FAGList implements ListInterface
{
    private static string $logFile = "../../../login/AOrd_Verz/Logging/FAG_List_debug.log.txt";
    
    public static function getListId(): string
    {
        #self::Log("in getlist");
        return 'FAG';
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
        $colsByTable = $meta->getColumnsForTables([$selId."_fza_geraet"]);
        
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
        
        $showCols = ["ge_id", "ge_type", "ge_sammlg", "ge_bezeich", "ge_baujahr", "ge_herst", "ge_komment", "ge_foto_1",  ];
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
            
            if ($fldName == 'ge_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 90 ,  "hozAlign" => "center", "headerSort" => true, "formatter" => 'html' ];
            } else if ($fldName == 'ge_sammlg' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" => 120 , "headerfilter" => false, "formatter" => 'html' ];
            } elseif ($fldName == 'ge_komment') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 250, "headerSort" => false, "formatter" => 'textarea' ];
            } elseif ($fldName == 'ge_bezeich') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "formatter" => 'textarea' ];
            } else if ($fldName == 'ge_foto_1' || $fldName == 'ad_beschreibg') {
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
            'textFilters' => ['ge_sammlg', 'ge_baujahr', 'ge_beschr', ],
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
        $tableName = "{$selId}_fza_geraet";
        
        # self::log("project $project");
        $stmt = $pdo->query("SELECT * FROM {$tableName} ORDER BY ge_id");
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
            $ge_id = (int) ($row['ge_id'] ?? 0);
            $row['action'] = "<a class='btn action-link' href='FAGEdit.php?ge_id={$ge_id}&ei_id={$selId}'>Edit</a>";
            
            // Ergänzung des Namens zur Abkürzung ge_sammlg
            if (!empty($row['ge_sammlg'])) { 
                $saName = $this->findSaName($row['ge_sammlg'], $rowSammlg);
                if ($saName !== null) {
                    $row['ge_sammlg'] .= "<br>" . htmlspecialchars($saName);
                }
                if ($row['ge_sammlg'] == "Nix") {
                    $row['ge_sammlg'] = "";
                }
            }
            
            // Hersteller ergänzen
            if (!empty($row['ge_herst']) && isset($herst_arr[$row['ge_herst']])) {
                $row['ge_herst'] =  htmlspecialchars($herst_arr[$row['ge_herst']]);
            }

            /** Bilder / Pdfs Anzeigen */
            #$aord = $row['ad_sg'] . "/" . $row['ad_subsg'] . "/";
            $d_path = "../../../login/AOrd_Verz/"; 
            if ($row['ge_foto_1'] != "") {
                $bild1 = $row['ge_foto_1'];
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
                        $dPathPict = $d_path . "$selId/MaG/"; //dieser Pfad gilt nicht für Biler mit _W_ oder _N_ bzw [urhId]-[AufnDat]-[-W|N-}OrigBildName
                    }
                    $row['ge_foto_1'] = "<a href='$dPathPict$bild1' target='_blank'><img src='$dPathPict$bild1' alt='$bild1' height='200' ></a>";
                } else {
                    $dPathNoPict = $d_path . "$selId/$aord/";
                    $row['ge_foto_1'] = "<a href='$dPathNoPict$bild1' target='_blank'>$bild1</a>";
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
