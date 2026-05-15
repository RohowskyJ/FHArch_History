<?php

/** 
 * Erstellen der Header- Ttiteln für Mitglieder- Listen
 * 
 */

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "ME_ListeTableConfig_php-error.log.txt");

class ME_ListeTableConfig {
    /**
     * Liefert die Spalten-Konfiguration für tabulator.js basierend auf dem Listentyp
     * @param string $listType
     * @return array
     */

    private static string $logFile = "ME_ListeTableConfig_debug.log.txt";
    
    public static function getColumns(string $listType, PDO $pdo): array {
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten
    
        $meta = new BS_TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["fv_mand_erl", "fv_mandant"]);
      
        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = []; // alternative Titel zu den Feld- Kommentaren
          
        $altTitel = [];       
        
        switch ($listType) {     
            case "Alle":
            default: 
                $showCols = ["fu_id", "ei_id", "fu_erlauben"];
               #$altTitel = ["fd_name" => "Name", "fd_adresse" => "Adresse" ];
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
        
            if ($fldName == 'fu_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" =>  8 , "hozAlign" => "center", "headerFilter" => "input", "formatter" => $format ];
            } elseif ($fldName == 'ei_id' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName , "formatter" => $format ];
            } elseif ($fldName == 'fu_erlauben' ) {
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