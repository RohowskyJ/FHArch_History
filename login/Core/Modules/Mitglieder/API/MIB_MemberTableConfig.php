<?php
namespace Fharch\Core\Modules\Mitglieder\API;

use PDO;
use Fharch\Core\Services\TableColumnMetadata;

/** 
 * Erstellen der Header- Ttiteln für Mitglieder- Listen
 * 
 */

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', "MIB_TableConfig_php-error.log.txt");

class MIB_MemberTableConfig {
    /**
     * Liefert die Spalten-Konfiguration für tabulator.js basierend auf dem Listentyp
     * @param string $listType
     * @return array
     */

    private static string $logFile = "MIB_TableConfig_debug.log.txt";
    
    public static function getColumns(string $listType, PDO $pdo): array {
        /*
        $json = json_encode($pdo);
        self::log(__LINE__ . " listType $listType");
        self::log(__LINE__ . " PDO $json");
        # self::log(__LINE__ . " VF_Database instance: " . var_export($vfDatabase, true));
        self::log(__LINE__ . " PDO object: " . var_export($pdo, true));
        
        $json = json_encode($pdo);
        self::log(__LINE__ . " PDO $json");
        */
        $sortNo = []; // nicht zu sortierende Spalten
        $hideNo = []; // nicht versteckbare Spalten
        $editable = []; // editierbare Spalten
    
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $colsByTable = $meta->getColumnsForTables(["fv_mitglieder","fv_mi_bez"]);
        
       // $json = json_encode($meta);fv_mitglieder
       //self::log(__LINE__ . " Meta $json");
        
        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = []; // alternative Titel zu den Feld- Kommentaren
          
        $altTitel = [
        ];       
        
        $curjahr = date("Y");
        $currMon = date('m');
        $ljahr = date("Y") - 1;
        $ljahr = $ljahr . "-12-31";
        
        $j_2 = $curjahr - 2;
        $j_1 = $curjahr - 1;
        $j_p = $curjahr + 1;

        switch ($listType) { 
            case "sticht":
                $showCols = ['mi_name', 'mi_id','mi_mtyp',
                'c-1', 'c-0', 'c+1',
                'mi_m_beitr_bez_bis','mi_m_abo_bez_bis','mi_m_beitr_bez','mi_m_abo_bez','Korrektur'
                    ];
                break;
            case "bezahlt":
                $showCols = ['mi_name', 'mi_id','mi_mtyp',
                'c-1', 'c-0', 'c+1',
                'mi_m_beitr_bez_bis','mi_m_abo_bez_bis','mi_m_beitr_bez','mi_m_abo_bez','Korrektur'
                    ];
                break;
            case "Alle":
            default: 
                $showCols = [ 'mi_name', 'mi_id','mi_mtyp', 
                'c-1', 'c-0', 'c+1',
                'mi_m_beitr_bez_bis','mi_m_abo_bez_bis','mi_m_beitr_bez','mi_m_abo_bez','Korrektur']; 
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
        # $TabTitles[] = ["title" => "Aktion", "field" => "action", "width" =>  6 , "hozAlign" => "center",  "headerSort" => false ,  "formatter" => "html"];
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
            
           if ($fldName == 'c-1') { 
               if ($currMon <= 11)  { // für Hauptteil des Jahres
                   
               }

               $TabTitles[] = [ 
                        // create column group
                        "title" => $j_1,
                        "columns" => [
                            ["title" => "MB Bez",
                                "field" => "M_1",
                                "hozAlign" => "center",
                                "headerSort" => false, 
                                "editor" => false, // Nicht direkt editierbar, Klick-Handler nutzen
                                "formatter" => "paymentFormatter"
                            ],
                            [ "title" => "Abo Bez",
                                "field" => "A_1",
                                "hozAlign" => "center",
                                "headerSort" => false, 
                                "editor" => false, // Nicht direkt editierbar, Klick-Handler nutzen
                                "formatter" => "paymentFormatter"
                            ]
                        ],
                    ];
            } elseif ($fldName == 'c-0') {
                $TabTitles[] = [// create column group
                        "title" => $curjahr,
                        "columns" => [
                            ["title" => "MB Bez",
                                "field" => "M_0",
                                "hozAlign" => "center",
                                "headerSort" => false, 
                                "editor" => false, // Nicht direkt editierbar, Klick-Handler nutzen
                                "formatter" => "paymentFormatter"
                            ],
                            ["title" => "Abo Bez",
                                "field" => "A_0",
                                "hozAlign" => "center",
                                "headerSort" => false, 
                                "editor" => false, // Nicht direkt editierbar, Klick-Handler nutzen
                                "formatter" => "paymentFormatter"
                            ],
                            ["title" => "MB + Abo Bez",
                                "field" => "MA_0",
                                "hozAlign" => "center",
                                "headerSort" => false,
                                "editor" => false, // Nicht direkt editierbar, Klick-Handler nutzen
                                "formatter" => "paymentFormatter"
                            ]
                        ],
                    ];
            } elseif ($fldName == 'c+1') {
                if ($currMon >= 11)  { // für Umstellungszeit YE
                    
                }
                $TabTitles[] = [
                        // create column group
                    "title" => $j_p, 
                        "columns" => [
                            ["title" => "MB Bez",
                                "field" => "M_p",
                                "hozAlign" => "center",
                                "headerSort" => false, 
                                "editor" => false, // Nicht direkt editierbar, Klick-Handler nutzen
                                "formatter" => "paymentFormatter"
                            ],
                            ["title" => "Abo Bez",
                                "field" => "A_p",
                                "hozAlign" => "center",
                                "headerSort" => false, 
                                "editor" => false, // Nicht direkt editierbar, Klick-Handler nutzen
                                "formatter" => "paymentFormatter"
                            ],
                            ["title" => "MB + Abo Bez",
                                "field" => "MA_p",
                                "hozAlign" => "center",
                                "headerSort" => false, 
                                "editor" => false, // Nicht direkt editierbar, Klick-Handler nutzen
                                "formatter" => "paymentFormatter"
                            ]
                        ],
                    ];
            } elseif ($fldName == 'mi_m_beitr_bez_bis') {
                $TabTitles[] = [ "title" => $titel, "field" => $fldName, "headerFilter" => false,
                    "formatter" => "yearFormatter",  // as string placeholder
                    "hozAlign" => "center",
                    // "cellClick" => "handleYearFieldClick"  // placeholder for JS function name
                ];
            } elseif ($fldName == 'mi_m_abo_bez_bis') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerFilter" => false, 'formatter' => 'yearFormatter',
                        "hozAlign" => "center",
                        // "cellClick" => "handleYearFieldClick"
                    ];
            } elseif ($fldName == 'mi_name') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "sorter" => "string", "headerFilter" => "input", ]; // "formatter" => $format   headerWordWrap:true
            } elseif ($fldName == 'mi_id') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "sorter" => "number", "headerFilter" => "input", ]; // "formatter" => $format   headerWordWrap:true
            } elseif ($fldName == 'Korrektur') {
                $TabTitles[] = [
                    "title" => "Korrektur",
                    "field" => "Korrektur", // Feldname kann frei gewählt sein
                    "formatter" => "korrekturFormatter", // Name des Formatters, der im Frontend definiert wird
                    "headerFilter" => false,
                    "headerSort" => false,
                    "hozAlign" => "center"
                ];
            } else {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "headerFilter" => false,  "headerSort" => false ];  // "headerSort" => false
            }

        }
        /*
        $json = json_encode($TabTitles);
        self::log("Tabtitles $json");
        */
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