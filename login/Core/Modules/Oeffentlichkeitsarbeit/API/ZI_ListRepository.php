<?php
namespace Fharch\Core\Modules\Oeffentlichkeitsarbeit\API;

use \PDO;
use Fharch\Core\Services\TableColumnMetadata;

const GrafFiles = array("gif","ico","jpeg","jpg","png","tiff"); 

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class ZI_ListRepository {
    private PDO $pdo;
    protected static string $logFile = 'ZI_ListRepository_debug.log.txt';
    private $ztNr;
    
    public function __construct(\PDO $pdo, int $ztNr) {
        $this->pdo = $pdo;
        $this->ztNr = $ztNr;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getZInhalt(string $listType, int $custom, ?string $search = null ): array {
        $sql = "SELECT * FROM oe_zeitung_".$custom;
        $where = [];
        $params = [];
        
        switch ($listType) {
            case 'Alle':
            default:
                # $where[] = "mi_name != ''";
                $orderBy = "ORDER BY ih_id";
        }
        
        if ($search !== null && trim($search) !== '') {
            $where[] = "ih_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " $orderBy";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $rows = $stmt->fetchAll();
 
        
        if (count($rows) == 0) { // wenn keine Daten vorhanden
            return $rows;
        }
        
        // Daten vor der Rückgabe anpassen
        foreach ($rows as &$row) {
            $this->modifyRow($row, $listType, $custom);
        }
        
        return $rows;
    }
    
    protected function modifyRow(array &$row, $tabTyp,  $custom)
    {
        // $json = json_encode($row);
        // $this->log("modifyRow wurde aufgerufen, row $json");
        $ih_id = $row['ih_id']; // ?? 0;
        
        $row['action'] = "<a href='Z_InhalteEdit.php?ID={$ih_id}&ztNr=$custom'>Edit</a>";
       
       //    $showCols = ["ih_id", "ih_datum", "ih_name", "ih_ausg", "ih_teaser", "ih_text", "ih_bild_1", "ih_bild_2"];
       /*
        switch  ($row['ih_medium']) 
        {
            CASE ('TV'):
                break;
            CASE ('URL'):
                if ($row['ih_text'] == "") {
                    $row['ih_text'] = $row['ih_web_text'];
                }
                $row['ih_name'] .= "<br>" . $row['ih_web_seite'];
                break;
            CASE ('PR'):
            default:
               
        }
       
        */
       
        unset($row['ih_jahrgang']);
        unset($row['ih_jahr']);
        unset($row['ih_nr']);
        unset($row['ih_ssg']);
        unset($row['ih_gruppe']);
        unset($row['ih_titelerw']);
        unset($row['ih_tel']);
        unset($row['ih_fax']);
        unset($row['ih_seite']);
        unset($row['ih_spalte']);
        unset($row['ih_fwehr']);
        unset($row['ih_changed-id']);
        unset($row['ih_changed_at']);
 
        return true;
    }
    
    /** Funktion zum schreiben von Log- Eintägen der Klasse */
    protected static function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }
}