<?php

namespace FSArch\Login\Basis;

use PDO;
use FSArch\Login\Basis\BS_TableColumnMetadata;

/** 
 * Erstellen der Header- Ttiteln für Mitglieder- Listen
 * 
 */

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "RO_ListeTableConfig_php-error.log.txt");

class RO_ListeTableConfig {
    /**
     * Liefert die Spalten-Konfiguration für tabulator.js basierend auf dem Listentyp
     * @param string $listType
     * @return array
     */

    private static string $logFile = "RO_ListeTableConfig_debug.log.txt";
    
    public static function getColumns(string $listType, PDO $pdo): array {
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten
    
        $meta = new BS_TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["fv_rolle", "fv_rollen_beschr"]);
      
        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = []; // alternative Titel zu den Feld- Kommentaren
          
        $altTitel = [];       
        
        switch ($listType) {     
            case "Alle":
            default: 
                $showCols = ["fr_id", "fr_aktiv", "fl_beschreibung", "fl_modules"];
                #$altTitel = ["fd_name" => "Name", "fd_adlesse" => "Adresse" ];
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
            //"fr_aktiv", "fl_beschreibung", "fl_modules",  "fd_ort", "fd_tel", "fd_email"];
            if ($fldName == 'fr_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" =>  8 , "hozAlign" => "center", "formatter" => $format ];
            } elseif ($fldName == 'fr_aktiv' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" =>  8 , "formatter" => $format ];
            } elseif ($fldName == 'fl_beschreibung' || $fldName == 'fl_modules') {
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