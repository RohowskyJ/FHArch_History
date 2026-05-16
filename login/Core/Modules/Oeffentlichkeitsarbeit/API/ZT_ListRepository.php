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
class ZT_ListRepository {
    private PDO $pdo;
    protected static string $logFile = 'ZT_ListRepository_debug.log.txt';
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getZeitungen(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM oe_zeitungen ";
        $where = [];
        $params = [];
        
        switch ($listType) {
            case 'Alle':
            default:
                # $where[] = "mi_name != ''";
                $orderBy = "ORDER BY zt_id";
        }
        
        if ($search !== null && trim($search) !== '') {
            $where[] = "pr_name LIKE :search";
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
        $zt_id = $row['zt_id']; // ?? 0;
        
        #if ($tabTyp != "Extern") {
            $row['action'] = "<a href='ZeitungEdit.php?ID={$zt_id}'>Edit</a>";
        #}
   
        $row['inhalt']  = "<a href='Z_InhalteList.php?ID={$zt_id}'>Inhalt</a>";
        
        if ($row['zt_internet'] != "")   {
            $enet = $row['zt_internet'];
            $row['zt_internet'] = "<a href='http://$enet' target='_blank' >$enet </a>";
        }
      
       
        foreach ($neu as $key => $val) {
            if (substr($key,0,3) != 'zt_') {
                unset($neu[$key]);
            }
        }
 
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