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
ini_set('error_log', "ZI_TableConfig_php-error.log.txt");

class ZI_ListTableConfig {
    /**
     * Liefert die Spalten-Konfiguration für tabulator.js basierend auf dem Listentyp
     * @param string $listType
     * @return array
     */

    private static string $logFile = "ZI_TableConfig_debug.log.txt";
    
    public static function getColumns(string $listType, PDO $pdo, $ztNr): array {
 
     
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten
    
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["oe_zeitung_".$ztNr]);
        
        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = []; // alternative Titel zu den Feld- Kommentaren
          
        switch ($listType) {     
            case "Alle":
                
            default: 
                $showCols = ["ih_id", "ih_jahr", "ih_kateg", "ih_sg", "ih_titel", "ih_autor"];
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
            
            if ($fldName == 'ih_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" =>  8 ,  "hozAlign" => "center", "formatter" => 'html' ];
            } else if ($fldName == 'pr_text') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "formatter" => 'textarea' ];
            } else if ($fldName == 'pr_bild_1' || $fldName == 'pr_bild_2') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "formatter" => 'html' ];
            } else {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerFilter" => "input", "formatter" => 'plaintext' ];
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