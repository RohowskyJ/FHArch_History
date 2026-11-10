<?php

declare(strict_types=1);

namespace FWHist\Modules\Inventar\Lists;

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;
use InvalidArgumentException;

const GrafFiles = array("gif","ico","jpeg","jpg","png","tiff"); 

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/InvList_php-error.log.txt");

final class InventList implements ListInterface
{
    /** Felder, die an den Browser ausgeliefert werden (Allowlist = Spalten der Liste, sonst nichts) */
    private const SHOW_COLS = ["in_id", "in_sammlg", "in_bezeichnung", "in_hersteller", "in_foto_1"];

    private static string $logFile = "../../../login/AOrd_Verz/Logging/InvList_debug.log.txt";

    public static function getListId(): string
    {
        #self::Log("in getlist");
        return 'IN';
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
        $colsByTable = $meta->getColumnsForTables([$selId."_inventar"]);

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

        $showCols = self::SHOW_COLS; // anzuzeigende Spalten
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

            if ($fldName == 'in_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 90 ,  "hozAlign" => "center", "headerSort" => true, "formatter" => 'html' ];
                /*
            } else if ($fldName == 'in_foto_date' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" => 90 , "headerfilter" => false, "formatter" => 'plaintext' ];
            } else if ($fldName == 'ad_sg'  ) { 
                $TabTitles[] = 
                    ["title" => "Archiv- Ordnungs- Ebene",
                    "columns" => [
                        ["title" => "1", "field" => "ad_sg", "hozalign" => "center", "headerSort" => false,  "width" => "30"],
                        ["title" => "2", "field" => "ad_subsg", "hozalign" => "center", "headerSort" => false, "width" => "30"],
                        ["title" => "3", "field" => "ad_lcsg", "hozalign" => "center", "headerSort" => false, "width" => "30"],
                        ["title" => "4", "field" => "ad_lcssg", "hozalign" => "center", "headerSort" => false, "width" => "30"],
                        ["title" => "5", "field" => "ad_lcssg_s0", "hozalign" => "center", "headerSort" => false, "width" => "30"],
                        ["title" => "6", "field" => "ad_lcssg_s1", "hozalign" => "center", "headerSort" => false, "width" => "30"],
                    ],
                ];
                */
            } elseif ($fldName == 'in_bezeichnung') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "formatter" => 'html' ];
            } else if ($fldName == 'in_foto_1' || $fldName == 'in_foto_2') {
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
            'textFilters' => ['in_sammlg', 'in_bezeichnung', ],
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
        $tableName = "{$selId}_inventar";
        
        # self::log("project $project");
        $stmt = $pdo->query("SELECT * FROM {$tableName} ORDER BY in_id");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

       # $archord = $pdo->query('SELECT al_sg, al_lcsg, al_lcssg, al_lcssg_s0, al_lcssg_s1, al_bezeich, al_sammlung FROM hi_archord_erw')->fetchAll(PDO::FETCH_KEY_PAIR);
       # $sammlg = $pdo->query('SELECT sa_grp, sa_name, sa_sammlg FROM hi_bundesld')->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($rows as &$row) {
            $in_id = (int) ($row['in_id'] ?? 0);
            // Action-Link immer (Detail-/Edit-Formular); ohne Schreibrecht nur "Anzeigen" (Formular ist dann schreibgeschützt)
            $actionLabel = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='InvEdit.php?in_id={$in_id}&ei_id={$selId}'>{$actionLabel}</a>";
           
            /** Bilder / Pdfs Anzeigen */
            //";
            $aord = 'INV';
            $d_path = "../../../login/AOrd_Verz/"; 
            if ($row['in_foto_1'] != "") {
                $bild1 = $row['in_foto_1'];
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
                    $fo_arr = explode("-", $bild1);
             
                    if (count($fo_arr) >= 3) { // neues Format , Bilder in Foto-Lib
                     
                        if ($fo_arr[2] == "W") {
                            $urh = $fo_arr[0];
                            $verz = $fo_arr[1] . '/';
                            $p = $d_path.  $urh . "/" . "09/06/" . $verz . $bild1;
                           
                            $row['in_foto_1'] = "<a href='$p' target='Bild 1' > <img src='$p' alt='$p'height='200'> <br> $bild1 </a>";
                            /*
                            #$row['in_foto_1'] = "<a href='$p' target='Bild 1' > <img src='$p' alt='$p' style='max-height:200px; max-width:200px; max-width:auto; max-height:auto;'> <br> $bild1 </a>";
                            
                            $row['in_foto_1'] = "<a href='$p' target='Bild 1'>
                                 <img src='$p' alt='$p' style='max-height:200px; max-width:200px; display:block; margin:auto;'>
                                  <br>$bild1
                                  </a>";
                            */
                        }
                    } else {  // altes Format
                        $p = $d_path . $selId . "/" .  "INV/" . $bild1;
                        $row['in_foto_1'] = "<a href='$p' target='Bild 1' > <img src='$p' alt='$p'height='200'> <br> $bild1 </a>";
                    }
                    
                } else {
                    $dPathNoPict = $d_path . "$selId/INV/";
                    $row['in_foto_1'] = "<a href='$dPathNoPict$bild1' target='_blank'>$bild1</a>";
                }
            }
            // Nur Listenfelder ausliefern: SELECT * liest alles, der Rest (Werte, Änderungsdaten, Beschreibungen ...)
            // verlässt den Server nicht - auch nicht für ONU-ALL/ONU-GAST
            $row = array_intersect_key($row, array_flip([...self::SHOW_COLS, 'action']));
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
