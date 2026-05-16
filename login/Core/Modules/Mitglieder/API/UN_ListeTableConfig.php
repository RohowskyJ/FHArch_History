<?php
namespace Fharch\Core\Modules\Mitglieder\API;

use \PDO;
use Fharch\Core\Services\TableColumnMetadata;

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "UN_TableConfig_php-error.log.txt");

/**
 * Definition der Tabellen- Header- Zeile 
 * 
 * @author josef
 *
 */
class UN_ListeTableConfig {
    /**
     * Liefert die Spalten-Konfiguration für tabulator.js basierend auf dem Listentyp
     * 
     * @param string $listType
     * @return array
     */

    private static string $logFile = "UN_ListeTableConfig_debug.log.txt";
    
    public static function getColumns(string $listType, \PDO $pdo): array {
        /*
        $json = json_encode($pdo);
        self::log(__LINE__ . " listType $listType");
        self::log(__LINE__ . " PDO $json");
        # self::log(__LINE__ . " VF_Database instance: " . var_export($vfDatabase, true));
        # self::log(__LINE__ . " PDO object: " . var_export($pdo, true));
        
        $json = json_encode($pdo);
        # self::log(__LINE__ . " PDO $json");
       */
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten
    
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["fv_unterst"]);
       // $json = json_encode($meta);
        //self::log(__LINE__ . " Meta $json");
        
        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = []; // alternative Titel zu den Feld- Kommentaren
          
        $altTitel = [];       
        
        switch ($listType) {    
            case "Alle":
            case "Aktive":
            case "InAktive":
                $showCols = ['fu_id', 'fu_kateg', 'fu_aktiv',  'fu_orgname',  'fu_anrede', 
                             'fu_name',  'fu_adresse', 'fu_tel', 'fu_email'];
                 break; 
            case "WeihnP": 
                $showCols = [ 'fu_id', 'fu_orgname', 'fu_anrede', 'fu_tit_vor', 'fu_vname',
                             'fu_name', 'fu_tit_nach', 'fu_adresse', 'fu_plz', 'fu_ort' ];
                break;
            case "AdrListE":
            case "AdrListA":
                $showCols = [ 'fu_id', 'fu_orgname', 'fu_anrede', 'fu_tit_vor', 'fu_vname',
                             'fu_name', 'fu_tit_nach', 'fu_adresse', 'fu_plz', 'fu_ort' ];
                break; 
            default: 
                $showCols = [ "fu_id", 'fu_kateg', 'fu_orgname', "fu_name", "fu_adresse",  "fu_orgname", "fu_adresse", "fu_tel", "fu_email"];
        }
      /*
        $lTitel = ["Alle" => "Alle jemals eingtragenen Unterstützer",
            "Aktive" => "Aktive Unterstützer   ",
            "InAktive" => "In- Aktive Unterstützer   ",
            "WeihnP" => "Für den Versand vorgesehene Einträge    ",
            "AdrListE" => "Adress-Liste für die Aussendung, Änderungen  ",
            "AdrListV" => "Adress-Liste für die Aussendung, Versand    "];
      */  
        
        /** erstellen der Titel Header */
        $colComment = $meta->getCommentsMap();
        $colStyles  = $meta->getStylesMap();
        $colTypes = $meta->getTypesMap();
        $colLength = $meta->getMaxLengthsMap();
        /*
        $json = json_encode($colComment);
        self::log( __LINE__ . " Kommentare $json  ");
        */
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
            if ($fldName == 'mi_gebtag') {
                $format =   "html" ;
            }
   
            $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerFilter" => "input", "formatter" => $format ];
           
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