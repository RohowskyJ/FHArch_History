<?php

namespace Fharch\Core\AllgVerw\API;

use PDO;
use Fharch\Core\Services\TableColumnMetadata;

/** 
 * Erstellen der Header- Ttiteln für Mitglieder- Listen
 * 
 */

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "BenutzerTableConfig_php-error.log.txt");

class ABK_ListTableConfig {
    /**
     * Liefert die Spalten-Konfiguration für tabulator.js basierend auf dem Listentyp
     * @param string $listType
     * @return array
     */

    private static string $logFile = "ABK_TableConfig_debug.log.txt";
    
    public static function getColumns(string $listType, PDO $pdo): array {
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten
    
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["fv_adm_mail", "fv_ben", "fv_ben_dat"]);
      
        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = []; // alternative Titel zu den Feld- Kommentaren
          
        $altTitel = [];       
        /*
        $lTitel = ["Alle" =>"Alle Abkürzungen"
            ,'MA_F' => 'Abk. Motorisierte Fahrzeuge'
            ,'MA_G' => 'Abk. Motorisierte Geräte'
            ,'MU_F' => 'Abk. Muskelgezogene Fahrzeuge'
            ,'MU_G' => 'Abk. Muskelbetriebene Geräte'
            ,'ORG'  => 'Abk. Organisation '];
        */
        
        switch ($listType) {     
            case "Alle":
                $showCols = [ "ab_id", "ab_grp", "ab_abk",'ab_bezeichn', 'ab_gruppe'];
                $altTitel = ["fd_name" => "Name", "fd_adresse" => "Adresse" ];
                break;
            default: 
                $$showCols = [ "ab_id", "ab_grp", "ab_abk",'ab_bezeichn', 'ab_gruppe'];
                $altTitel = ["fd_name" => "Name", ];
        }
           
        /** erstellen der Titel Header */
        $colComment = $meta->getCommentsMap();
        $colStyles  = $meta->getStylesMap();
        $colTypes = $meta->getTypesMap();
        $colLength = $meta->getMaxLengthsMap();
      
        $TabTitles[] = ["title" => "Aktion", "field" => "action", "width" =>  6 , "hozAlign" => "center",  "headerSort" => false ,  "formatter" => "html" ];
        foreach ($showCols as $fldName ) { 
           $titel = "";
            if (isset($altTitel[$fldName]) AND $altTitel[$fldName] !=  "" ) {
                $titel = $altTitel[$fldName];
            } elseif (isset($colComment[$fldName]) and $colComment[$fldName] !=  "" ) {
                $titel = $colComment[$fldName];
            } else {
                $titel = ucfirst($fldName);
            }
            
            $format = "plaintext";
        
            if ($fldName == 'ab_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" =>  8 , "hozAlign" => "center", "headerFilter" => "input", "formatter" => $format ];
            
            } elseif ($fldName == 'fd_tel' || $fldName == 'fd_email') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "formatter" => $format ];
            } else {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerFilter" => "input", "formatter" => $format ];
            }
           
        }
        $json = json_encode($TabTitles);
        
        return $TabTitles;
    }
    
    protected static function log(string $message): void
    {
        $timestamp = date("Y-m-d H:i:s");
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}
 ?>