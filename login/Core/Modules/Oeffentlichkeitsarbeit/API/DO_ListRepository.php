<?php
namespace Fharch\Core\Modules\Oeffentlichkeitsarbeit\API;

use \PDO;
use Fharch\Core\Services\TableColumnMetadata;

const GrafFiles = array("gif","ico","jpeg","jpg","png","tiff"); 

/**
 * Liest Daten aus der Dokumentendatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class DO_ListRepository {
    private \PDO $pdo;
    protected static string $logFile = 'DO_ListRepository_debug.log.txt';
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Dokumenten-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getDoku(string $listType,  ?string $search = null ): array {
        $sql = "SELECT * FROM oe_dokumente ";
        $where = [];
        $params = [];
        
        switch ($listType) {
            case 'Alle':
            default:
                
                $orderBy = "ORDER BY dk_id";
        }
        
        if ($search !== null && trim($search) !== '') {
            $where[] = "dk_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " $orderBy";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $rows = $stmt->fetchAll();
        
        // Daten vor der Rückgabe anpassen
        foreach ($rows as &$row) {
            $this->modifyRow($row, $listType);
        }
        
        return $rows;
    }
    
    protected function modifyRow(array &$row, $tabTyp)
    {
        // $json = json_encode($row);
        // $this->log("modifyRow wurde aufgerufen, row $json");
        $dk_id = $row['dk_id']; // ?? 0;
        
        #if ($tabTyp != "Extern") {
            $row['action'] = "<a href='DokuEdit.php?ID={$dk_id}'>Edit</a>";
        #}
      
        
        $dsn = [];    
        $p2dsn = $row['dk_path2dsn'];
        
        $d_path = "../../../../login/AOrd_Verz/Downloads/$p2dsn/";
        
        if ($row['dk_dsn'] != "") {
            $dsn = $row['dk_dsn'];
            $row['dk_dsn'] = "<a href='$dpath$dsn' target='_blank'>$dsn</a>";
        }
        if ($row['dk_dsn_2'] != "") {
            $dsn = $row['dk_dsn_2'];
            $row['dk_dsn'] .= "<br><a href='$dpath$dsn' target='_blank'>$dsn</a>";
        }
       
        unset($row['dk_jahrgang']);
        unset($row['dk_jahr']);
        unset($row['dk_nr']);
        unset($row['dk_ssg']);
        unset($row['dk_gruppe']);
        unset($row['dk_titelerw']);
        unset($row['dk_tel']);
        unset($row['dk_fax']);
        unset($row['dk_seite']);
        unset($row['dk_spalte']);
        unset($row['dk_fwehr']);
        unset($row['dk_changed-id']);
        unset($row['dk_changed_at']);
 
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