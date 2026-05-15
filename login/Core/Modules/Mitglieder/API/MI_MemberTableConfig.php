<?php
namespace Fharch\Core\Modules\Mitglieder\API;

use PDO;
use Fharch\Core\Services\TableColumnMetadata;

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "MI_TableConfig_php-error.log.txt");

/**
 * ADefinition der Tabellen- Header- Zeile 
 * 
 * @author josef
 *
 */
class MI_MemberTableConfig {
    /**
     * Liefert die Spalten-Konfiguration für tabulator.js basierend auf dem Listentyp
     * 
     * @param string $listType
     * @return array
     */

    private static string $logFile = "MemberTableConfig_debug.log.txt";
    
    public static function getColumns(string $listType, PDO $pdo): array {
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
        $colsByTable = $meta->getColumnsForTables(["fv_mitglieder"]);
       // $json = json_encode($meta);
        //self::log(__LINE__ . " Meta $json");
        
        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = []; // alternative Titel zu den Feld- Kommentaren
          
        $altTitel = ["mi_id" => "Mitgl. Nr.", "mi_m_typ" => "Mitgl. Typ","mi_org_typ" => "Organis. Typ", "mi_org_Name" => "Organisationn", 
            "mi_name" => "Name", "mi_titel" => "Titel", "mi_n_titel" => "Nachgest. Titel", "mi_dgr" => "FW Dienstgrad", "mi_anrede" => "Anrede",  
            "mi_tel_handy" => "Telefon", "mi_gebtag" => "Geburtstag - Alter", "mi_staat" => "Staat", "mi_plz" => "PlZ", "mi_ort" => "Ort", "mi_anschr" => "Anschrift",    
            "mi_fax" => "FAX Nr.","mi_email"=> "E-Mail", "mi_email_status" => "Status  E-Mail",
            "mi_vorst_funct" => "Vorstandsfunktion", "mi_ref_leit" => "Referatsleiter", "mi_ref_int_2" => "R2", "mi_ref_int_3"=> "R3", "mi_ref_int_4"=> "R4",
            "mi_sterbdat" => "Verstorben", "mi_beitritt" => "Beigetreten", "mi_austrdat" => "Ausgetreten",    
            "mi_beitr_bez_bis" => "MB nez. bis",  "mi_abo_bez_bis" => "ABO bez bis", "mi_abo_ausg" => "ABO ausgegeben",
            "mi_einv_art" => "Einverst. Erkl.", "vom:" => "Am:", "mi_ehrg" => "Verliehen", "mi_special_1" => "SonderNutzung", "mi_changed_id_v" => "str - change", "mi_changed_id" => "int Geändert von", "mi_changed_at" => "Geändert am"
        ];       
        
        switch ($listType) {     
            case "Alle":
            case "Mitgl":
            case "nMitgl":
                $showCols = [ "mi_id", "mi_m_typ", "mi_org_typ", "mi_org_name", "mi_name", "mi_anschr",  "mi_gebtag", "mi_email", "mi_tel_handy", "mi_ref_int_2"];
                break;
            case "Adrlist": 
                $showCols = [ "mi_id", "mi_name", "mi_anschr",  "mi_gebtag", "mi_email", "mi_tel_handy", "mi_ref_int_2"];
                break;
            case "BezL":
                $showCols = [ "mi_id", "mi_name", "mi_anschr",  "mi_gebtag", "mi_email", "mi_tel_handy", 'mi_m_beitr_bez', 'mi_m_abo_bez', 'mi_m_abo_ausg' ];
                break;
            default: 
               $showCols = [ "mi_id", "mi_name", "mi_anschr",  "mi_gebtag", "mi_email", "mi_tel_handy", "mi_ref_int_2"];
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