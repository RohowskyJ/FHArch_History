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
ini_set('error_log', "../../../login/AOrd_Verz/Logging/MedDatenList_php-error.log.txt");

final class MedDatenList implements ListInterface
{
    private const GRAF_FILES = ["gif", "ico", "jpeg", "jpg", "png", "tiff"];
    private const AUDIO_FILES = ["mp3", "aac", "wav"];
    private const VIDEO_FILES = ["mp4"];

    private static string $logFile = "../../../login/AOrd_Verz/Logging/MedDaten_debug.log.txt";

    public static function getListId(): string
    {
        #self::Log("in getlist");
        return 'MD';
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
        $colsByTable = $meta->getColumnsForTables([$selId."_dmedien"]);

        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = ["md_dsn_date" => "Dok.- Datum"]; // alternative Titel zu den Feld- Kommentaren

        #$altTitel = [];
        /*
         $lTitel = ["Alle" => "Alle Ehrungen ",
         "Bund" => "vom Bund (Staat) ausgegebenen Auszeichnungen ",
         "Bdld" => "vom Bundesland ausgegebenen Auszeichnungen ",
         "Gde"  => "von einer Gemeinde ausgegebenen Auszeichnungen ",
         "Vereine" =>  "von einer Verein ausgegebenen Auszeichnungen ",
         ];
         */

        $showCols = ["md_id", "md_aufn_datum", "md_beschreibg", "md_suchbegr", "md_dsn_1", "md_sammlg",   ]; 
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

            if ($fldName == 'md_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 90 ,  "hozAlign" => "center", "headerSort" => true, "formatter" => 'html' ];
            } else if ($fldName == 'md_aufn_datum' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" => 90 , "headerfilter" => false, "formatter" => 'plaintext' ];
            } elseif ($fldName == 'md_beschreibg') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "formatter" => 'html' ];
            } else if ($fldName == 'md_dsn_1' || $fldName == 'md_beschreibg') {
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
        // ad_sg/ad_subsg/ad_lcsg/ad_lcssg/ad_lcssg_s0/ad_lcssg_s1 werden nicht mehr
        // über einen eigenen Select-Filter angeboten, sondern ausschließlich über
        // die Kaskade im <fw-multi-select> (Archivordnung) gefiltert -- siehe
        // 'multiSelectFilter' in der lists[]-Konfiguration von ArchivList.php.
        return [
            /*
            'selectFilters' => [
            	/*
                'md_' => [
                    'label' => 'ArchivOrdnung',
                    'options' => ['01'=>'Lokaler FW. Dienst', '07'=>'	Feuerwehr- Fahrzeuge, Häuser, Geräte', '10'=>'Literatur', ''=>'',]
                ],
                  
                'ad_sammlg' => [
                    'label' => 'Sammlung',
                    'options' => ['MA_F'=>'Motor- Fahrzeuge', 'MA_G'=>'Motor- Geräte', 'MU_F'=>'Muskel-Fahrzeuge', 'MU_G'=>'Muskel-Geräte'],
                ],
            
            ],
            */
            'textFilters' => ['md_beschreibg', 'md_namen','md_suchbegr', ],
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
        $tableName = "{$selId}_dmedien";
        $sql = "SELECT * FROM {$tableName}";
        
        $params = [];
        if ($project !== null) {
            $sql .= " WHERE md_aufn_datum = :project";
            $params[':project'] = $project;
        }
        
        $sql .= " ORDER BY md_id";
        
        // Bereite das Statement vor
        $stmt = $pdo->prepare($sql);
    
        // Führe das Statement mit Parametern aus
        $stmt->execute($params);
        
        // Hole alle Ergebnisse als assoziatives Array
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        /*
        # self::log("project $project");
        $stmt = $pdo->query("SELECT * FROM {$tableName} ORDER BY md_id");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

       # $archord = $pdo->query('SELECT al_sg, al_lcsg, al_lcssg, al_lcssg_s0, al_lcssg_s1, al_bezeich, al_sammlung FROM hi_archord_erw')->fetchAll(PDO::FETCH_KEY_PAIR);
       # $sammlg = $pdo->query('SELECT sa_grp, sa_name, sa_sammlg FROM hi_bundesld')->fetchAll(PDO::FETCH_KEY_PAIR);
        */
        foreach ($rows as &$row) {
            $md_id = (int) ($row['md_id'] ?? 0);
            
            $label = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='MedDEdit.php?md_id={$md_id}&urh_id={$selId}'>{$label}</a>";
            if ($row['md_dsn_1'] == '0_Verz'){
                $row['action'] = "<a class='btn action-link' href='MedVEdit.php?md_id={$md_id}&med_id={$selId}'>{$label}</a>";
            }
            
            /** Bilder / Pdfs Anzeigen */
            $d_path = "../../../login/AOrd_Verz/"; //$selId/$aord";
            if ($row['md_dsn_1'] != "") {
                $bild1 = $row['md_dsn_1'];
                $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION));
                
                $audfile = false;
                foreach (self::AUDIO_FILES as $val ) {
                    if ($val == $ext ) {
                        $audfile = true;
                        break;
                    }
                }
                
                $graffile = false;
                foreach (self::GRAF_FILES as $val ) {
                    if ($val == $ext ) {
                        $graffile = true;
                        break;
                    }
                }
                
                $vidfile = false;
                foreach (self::VIDEO_FILES as $val ) {
                    if ($val == $ext ) {
                        $vidfile = true;
                        break;
                    }
                }
                
                if ($graffile) {
                    // Bild analysieren für _W_ _N_
                    $dPathPict = $d_path . "$selId/09/06/$project/"; //dieser Pfad gilt nicht für Biler mit _W_ oder _N_ bzw [urhId]-[AufnDat]-[-W|N-}OrigBildName
                    $row['md_dsn_1'] = "<a href='$dPathPict$bild1' target='_blank'><img src='$dPathPict$bild1' alt='$bild1' height='200' ></a>";
                } elseif ($audfile) { 
                    $dPathAudio = $d_path . "$selId/09/05/$project/"; //dieser Pfad gilt für Audio- Dateien
                    $row['md_dsn_1'] = "<a href='$dPathAudio$bild1' target='_blank'>$bild1</a>";
                } elseif ($vidfile) {
                    $dPathVideo = $d_path . "$selId/09/10/$project/"; //dieser Pfad gilt für Video- Dateien
                    $row['md_dsn_1'] = "<a href='$dPathVideo$bild1' target='_blank'>$bild1</a>";
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
