<?php

declare(strict_types=1);

namespace FWHist\Modules\Archivalien\Lists;

// Composer-Autoloader, bevor eigentliche Klassen verwendet werden.
require __DIR__ . '/../../../../vendor/autoload.php';

use FWHist\Services\ListInterface;
use PDO;
use FWHist\Services\TableColumnMetadata;
use InvalidArgumentException;
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Archivalien\API\DB_Archivalien;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../../../login/AOrd_Verz/Logging/ArchCntList_fatal_error.log', $message, FILE_APPEND);
    }
});
    

/** PHP- Error- Logging in Datei */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "../../../login/AOrd_Verz/Logging/ArchCntList_php-error.log.txt");


final class ArchCntList implements ListInterface
{
    private static string $logFile = "../../../../login/AOrd_Verz/Logging/ArchCntList_debug.log.txt";

    public static function getListId(): string
    {
        #self::Log("in getlist");
        return 'AD';
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
        $colsByTable = $meta->getColumnsForTables([$selId."_archivdat"]);

        $TabTitles =  [];
        $altTitel = [];
        $showCols = []; // anzuzeigende Spalten
        $altTitel = ["ad_doc_date" => "Dok.- Datum"]; // alternative Titel zu den Feld- Kommentaren

        $showCols = ["ad_sg", "ad_subsg", "ad_lcsg", "ad_lcssg",  "Archiv- Ordnung", "Anzahl",  ]; 
        
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

            if ($fldName == 'ad_sg' || $fldName == 'ad_sg' || $fldName == 'ad_subsg' || $fldName == 'ad_lcsg' || $fldName == 'ad_lcssg' || $fldName == 'ad_lcssg_s0' || $fldName == 'ad_lcssg_s1') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "width" => 90 ,  "hozAlign" => "center", "headerSort" => false, "formatter" => 'html' ];
            } elseif ($fldName == 'Archiv- Ordnung' ) {
                $TabTitles[] = ["title" => $titel, "field" => $fldName,  "width" => 300 , "headerfilter" => false, "formatter" => 'plaintext' ];
            } elseif ($fldName == 'Anzahl') {
                $TabTitles[] = ["title" => $titel, "field" => $fldName, "headerSort" => false, "formatter" => 'html' ];
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
        return [
            'selectFilters' => [
            ],
            'textFilters' => [],
        ];
    }

    /** Auslesen und Aubfbereiten der Daten */
    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {

        // Prüfe, ob $selId gesetzt und nicht leer ist
        if (empty($selId)) {
            throw new InvalidArgumentException('Parameter selId darf nicht leer sein.');
        }
        
        $arch = new DB_Archivalien(DB_GenericLog::getInstance(), $selId);
        
        // Baue den Tabellennamen dynamisch zusammen
        $tableName = "{$selId}_archivdat";
        
        // Angenommen $pdo ist deine PDO-Verbindung und $tabelle ist der Tabellenname (z.B. als Variable definiert)
        $sql = "SELECT ad_sg, ad_subsg, ad_lcsg, ad_lcssg, ad_lcssg_s0, ad_lcssg_s1, SUM(ei_id) AS Anzahl
        FROM {$tableName}
        GROUP BY ad_sg, ad_subsg, ad_lcsg, ad_lcssg
        ORDER BY ad_sg, ad_subsg, ad_lcsg, ad_lcssg";
        
        // PDO-Abfrage ausführen
        $stmt = $pdo->query($sql);
        
        // Alle Ergebnisse als assoziatives Array holen
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $s_cnt = 0;
        foreach ($rows as &$row) {
           
            $ad_id = (int) ($row['ad_id'] ?? 0);
            
            $Aro_Def = $Arl_Def = "";
         
            $cnt = (int) ($row['Anzahl'] ?? 0) / $selId;
            $s_cnt += $cnt;
            $arl_str = "";
            $adSg = (string) ($row['ad_sg'] ?? '');
            $adSubsg = (string) ($row['ad_subsg'] ?? '');
            $adLcsg = (string) ($row['ad_lcsg'] ?? '');
            $adLcssg = (string) ($row['ad_lcssg'] ?? '');
            $adLcssgS0 = (string) ($row['ad_lcssg_s0'] ?? '');
            $adLcssgS1 = (string) ($row['ad_lcssg_s1'] ?? '');

            if ($adLcsg !== "00" || $adLcssg !== "00") {
                $Arl_Def = $arch->getArchivordnungErweiterung($adSg, $adSubsg, $adLcsg, $adLcssg, $adLcssgS0, $adLcssgS1);
                $row['Archiv- Ordnung'] = $Arl_Def;
            } elseif ($adSg !== "" ) {
                $Aro_Def = $arch->getArchivordnungText($adSg, $adSubsg);
            } else {
                $arl_name =  "Falsche Archiv- Ordnungs- Bezeichnung";;
            }
            $row['Anzahl'] = $cnt;
            
          #  unset($row['fw_ab_name'], $row['fw_typ'], $row['fw_kommentar'], $row['fw_auszeich'], $row['fw_aermelw'], $row['fw_changed_at']);
        }
        $rows[] = ['ad_sg' => '', 'ad_sub_sg' => '', 'ad_lcsg' => '','ad_lcssg' => '','ad_lcssg' => '','ad_lcssg_s0' => '','ad_lcssg_1' => '','Archiv- Ordnung' => 'Summe aller Eintragungen: ','Anzahl' => $s_cnt] ;
        
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
