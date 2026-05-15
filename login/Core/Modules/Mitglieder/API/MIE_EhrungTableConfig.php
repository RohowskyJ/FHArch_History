<?php

namespace Fharch\Core\Modules\Mitglieder\API;

use PDO;
use Fharch\Core\Services\TableColumnMetadata;

/** 
 * Erstellen der Header- Ttiteln für Mitglieder- Listen
 * 
 */

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "MIE_TableConfig_php-error.log.txt");

class MIE_EhrungTableConfig {
    /**
     * Liefert die Spalten-Konfiguration für tabulator.js basierend auf dem Listentyp
     * @param string $listType
     * @return array
     */

    private static string $logFile = "MIE_TableConfig_debug.log.txt";
    
    public static function getColumns(string $listType, PDO $pdo): array {
        /*
        $vfDatabase = VF_Database::getInstance();
        $vfDatabase->setPrefix('fv_');
        $pdo = $vfDatabase->getPDO();
        */

        $json = json_encode($pdo);
        self::log(__LINE__ . " listType $listType");
        self::log(__LINE__ . " PDO $json");
        # self::log(__LINE__ . " VF_Database instance: " . var_export($vfDatabase, true));
        self::log(__LINE__ . " PDO object: " . var_export($pdo, true));
        
        $json = json_encode($pdo);
        self::log(__LINE__ . " PDO $json");

        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten
    
        $meta = new TableColumnMetadata($pdo, 'fhach_neu', false);
        $colsByTable = $meta->getColumnsForTables(["fv_mi_ehrungz"]);
       // $json = json_encode($meta);
        //self::log(__LINE__ . " Meta $json");
        
        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = []; // alternative Titel zu den Feld- Kommentaren
          
        $altTitel = ["me_id" => "Fortl. Nr.", "mi_id" => "Mitgl. Nr","me_ehrung" => "Ehrung", "me_eh_datum" => "Verleihungs- Datum", 
            "me_begruendg" => "Begründung", "me_bild1" => "Bild 1", "me_bild2" => "Bild2", "me_bild3" => "Bild3", "me_bild4" => "Bild 4",
            "me_changed_id" => "Geändert von", "me_changed_at" => "Geändert am"
        ];       
        
        switch ($listType) {     
            case "Alle":
            default: 
                $showCols = [ "me_id", "me_ehrung", "me_eh_datum",  "me_begruendg", "me_bild1"]; //  , "me_bild2"
        }
    
        /** erstellen der Titel Header */
        $colComment = $meta->getCommentsMap();
        $colStyles  = $meta->getStylesMap();
        $colTypes = $meta->getTypesMap();
        $colLength = $meta->getMaxLengthsMap();
       
        $json = json_encode($colComment);
        self::log( __LINE__ . " Kommentare $json  ");
        
        $TabTitles[] = ["title" => "Aktion", "field" => "action", "width" =>  6 , "hozAlign" => "center",  "headerSort" => false ,  "formatter" => "html"];
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
            if ($fldName == 'mi_gebtag') {
                $format =   "html" ;
            }
            if (stripos($fldName, '_bild')) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerFilter" => "input", "formatter" => 'html'];
            } else {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerFilter" => "input", "formatter" => $format ];
            }

        }
        $json = json_encode($TabTitles);
        self::log("Tabtitles $json");
        
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