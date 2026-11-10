<?php

declare(strict_types=1);

namespace FWHist\Modules\Medien\Lists;

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;
use InvalidArgumentException;

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/BerchtList_php-error.log.txt");

final class BerList implements ListInterface
{
    private const GRAF_FILES = ["gif", "ico", "jpeg", "jpg", "png", "tiff"];

    private static string $logFile = "../../../login/AOrd_Verz/Logging/BerichtList_debug.log.txt";

    public static function getListId(): string
    {
        #self::Log("in getlist");
        return 'BR';
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
        $colsByTable = $meta->getColumnsForTables([$selId."_archivdat"]);

        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = ["vb_doc_date" => "Dok.- Datum"]; // alternative Titel zu den Feld- Kommentaren

        #$altTitel = [];
        /*
         $lTitel = ["Alle" => "Alle Ehrungen ",
         "Bund" => "vom Bund (Staat) ausgegebenen Auszeichnungen ",
         "Bdld" => "vom Bundesland ausgegebenen Auszeichnungen ",
         "Gde"  => "von einer Gemeinde ausgegebenen Auszeichnungen ",
         ];
         */

        $showCols = ["vb_id", "vb_doc_date", "vb_beschreibg", "vb_type", "vb_doc_1", "vb_keywords", "vb_sammlg", "vb_sg",  ]; 
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

            if ($fldName == 'vb_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 90 ,  "hozAlign" => "center", "headerSort" => true, "formatter" => 'html' ];
            } else if ($fldName == 'vb_doc_date' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" => 90 , "headerfilter" => false, "formatter" => 'plaintext' ];
            } else if ($fldName == 'vb_sg'  ) { 
                $TabTitles[] = 
                    ["title" => "Archiv- Ordnungs- Ebene",
                    "columns" => [
                        ["title" => "1", "field" => "vb_sg", "hozalign" => "center", "headerSort" => false,  "width" => "30"],
                        ["title" => "2", "field" => "vb_subsg", "hozalign" => "center", "headerSort" => false, "width" => "30"],
                        ["title" => "3", "field" => "vb_lcsg", "hozalign" => "center", "headerSort" => false, "width" => "30"],
                        ["title" => "4", "field" => "vb_lcssg", "hozalign" => "center", "headerSort" => false, "width" => "30"],
                        ["title" => "5", "field" => "vb_lcssg_s0", "hozalign" => "center", "headerSort" => false, "width" => "30"],
                        ["title" => "6", "field" => "vb_lcssg_s1", "hozalign" => "center", "headerSort" => false, "width" => "30"],
                    ],
                ];
            } elseif ($fldName == 'vb_beschreibg') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "formatter" => 'html' ];
            } else if ($fldName == 'vb_doc_1' || $fldName == 'vb_beschreibg') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 250 , "headerSort" => false, "formatter" => 'html' ];
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
        // vb_sg/vb_subsg/vb_lcsg/vb_lcssg/vb_lcssg_s0/vb_lcssg_s1 werden nicht mehr
        // über einen eigenen Select-Filter angeboten, sondern ausschließlich über
        // die Kaskade im <fw-multi-select> (Archivordnung) gefiltert -- siehe
        // 'multiSelectFilter' in der lists[]-Konfiguration von ArchivList.php.
        return [
            'selectFilters' => [
            	/*
                'vb_sg' => [
                    'label' => 'ArchivOrdnung',
                    'options' => ['01'=>'Lokaler FW. Dienst', '07'=>'	Feuerwehr- Fahrzeuge, Häuser, Geräte', '10'=>'Literatur', ''=>'',]
                ],
                  
                'vb_sammlg' => [
                    'label' => 'Sammlung',
                    'options' => ['MA_F'=>'Motor- Fahrzeuge', 'MA_G'=>'Motor- Geräte', 'MU_F'=>'Muskel-Fahrzeuge', 'MU_G'=>'Muskel-Geräte'],
                ],
              */
            ],
            'textFilters' => ['vb_sammlg', 'vb_beschreibg','vb_keywords', ],
        ];
    }

    /** Auslesen und Aubfbereiten der Daten */
    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {

        // Baue den Tabellennamen dynamisch zusammen
        $tableName = "vb_bericht";
        
        # self::log("project $project");
        $stmt = $pdo->query("SELECT * FROM {$tableName} ORDER BY vb_id");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

       # $archord = $pdo->query('SELECT al_sg, al_lcsg, al_lcssg, al_lcssg_s0, al_lcssg_s1, al_bezeich, al_sammlung FROM hi_archord_erw')->fetchAll(PDO::FETCH_KEY_PAIR);
       # $sammlg = $pdo->query('SELECT sa_grp, sa_name, sa_sammlg FROM hi_bundesld')->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($rows as &$row) {
            $vb_id = (int) ($row['vb_id'] ?? 0);
            $row['action'] = "<a class='btn action-link' href='BerEdit.php?vb_id={$vb_id}'>Edit</a>";
            
            $vb_type = $row['vb_type'] ? $row['vb_type'] : NULL;
           
            if ($vb_type) {
                $vb_type = $row['vb_type'];
                $row['vb_type'] = $Arc_Type[$vb_type];
            }
            
            /** Bilder / Pdfs Anzeigen */
            $aord = $row['vb_sg'] . "/" . $row['vb_subsg'] . "/";
            $d_path = "../../../login/AOrd_Verz/"; //$selId/$aord";
            if ($row['vb_foto'] != "") {
                $bild1 = $row['vb_foto'];
                $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION));
                
                $graffile = false;
                foreach (self::GRAF_FILES as $val ) {
                    if ($val == $ext ) {
                        $graffile = true;
                        break;
                    }
                }
                
                if ($graffile) {
                    // Bild analysieren für _W_ _N_
                    
                    
                    
                    $dPathPict = $d_path . "$selId/09/06/"; //dieser Pfad gilt nicht für Biler mit _W_ oder _N_ bzw [urhId]-[AufnDat]-[-W|N-}OrigBildName
                    $row['vb_foto'] = "<a href='$dPathPict$bild1' target='_blank'><img src='$dPathPict$bild1' alt='$bild1' height='200' ></a>";
                }
            }
          #  unset($row['fw_ab_name'], $row['fw_typ'], $row['fw_kommentar'], $row['fw_auszeich'], $row['fw_aermelw'], $row['fw_changed_at']);
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
