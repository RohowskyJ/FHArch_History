<?php

namespace Fharch\Core\Modules\Oeffentlichkeitsarbeit\API;

use PDO;
use Fharch\Core\Services\TableColumnMetadata;

/** 
 * Erstellen der Header- Ttiteln für Mitglieder- Listen
 * 
 */

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "MP_TableConfig_php-error.log.txt");

class MP_ListTableConfig {
    /**
     * Liefert die Spalten-Konfiguration für tabulator.js basierend auf dem Listentyp
     * @param string $listType
     * @return array
     */

    private static string $logFile = "MP_TableConfig_debug.log.txt";
    
    public static function getColumns(string $listType, PDO $pdo): array {
 
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten
    
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["oe_marktplatz"]);
        
        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = []; // alternative Titel zu den Feld- Kommentaren
          
        $altTitel = []; 
        switch ($listType) {     
            case "Alle":
                
            default: 
                $showCols = ["bs_id", "bs_startdatum", "bs_kurztext", "bs_text", "bs_email_1", "bs_bild_1", "bs_bild_2"];
        }
    
        
        /** erstellen der Titel Header */
        $colComment = $meta->getCommentsMap();
        $colStyles  = $meta->getStylesMap();
        $colTypes = $meta->getTypesMap();
        $colLength = $meta->getMaxLengthsMap();
        /*
        $json = json_encode($colComment);
        self::log( __LINE__ . " Kommentare $json  ");
        */
        #if ($listType == 'Alle') {
            $TabTitles[] = ["title" => "Aktion", "field" => "action", "width" =>  6 , "hozAlign" => "center",  "headerSort" => false ,  "formatter" => "html" ];
        #}
        
        foreach ($showCols as $fldName ) { 
           $titel = "";
            if (isset($altTitel[$fldName]) AND $altTitel[$fldName] !=  "" ) {
                $titel = $altTitel[$fldName];
            } elseif (isset($colComment[$fldName]) and $colComment[$fldName] !=  "" ) {
                $titel = $colComment[$fldName];
            } else {
                $titel = ucfirst($fldName);
            }
            
            if ($fldName == 'bs_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" =>  8 ,  "hozAlign" => "center", "headerSort" => false, "formatter" => 'html' ];
            } else if ($fldName == 'bs_detailbeschr') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerSort" => false,  "formatter" => 'textarea' ];
            } else if ($fldName == 'bs_bild_1' || $fldName == 'bs_bild_2') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "formatter" => 'html' ];
            } else {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerFilter" => false, "headerSort" => false, "formatter" => 'plaintext' ];
            }
          
           
        }
        $json = json_encode($TabTitles);
        # self::log("Tabtitles $json");
        
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