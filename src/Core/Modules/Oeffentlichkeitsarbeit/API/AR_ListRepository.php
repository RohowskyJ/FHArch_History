<?php
namespace Fharch\Core\Modules\Oeffentlichkeitsarbeit\API;

use \PDO;
use Fharch\Core\Services\TableColumnMetadata;

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class AR_ListRepository {
    private PDO $pdo;
    protected static string $logFile = 'AR_ListRepository_debug.log.txt';
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getLinks(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM fv_falinks ";
        $where = [];
        $params = [];
        
        switch ($listType) {
            case 'Alle':
            default:
                # $where[] = "mi_name != ''";
                $orderBy = "ORDER BY fa_text";
        }
        
        if ($search !== null && trim($search) !== '') {
            $where[] = "mi_name LIKE :search";
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
        $fa_id = $row['fa_id']; // ?? 0;
        
        if ($tabTyp != "Extern") {
            $row['action'] = "<a href='ArLinkEdit.php?ID={$fa_id}'>Edit</a>";
        }
        
        if (!empty($row['fa_link'])) {
            $fa_link = $row['fa_link'];
            $row['fa_link'] = "<a href='http://$fa_link' target='_blank'>$fa_link</a>";
        }
        
        unset($row['fa_url_chkd']);
        unset($row['fa_url_obsolete']);
        unset($row['fa_changed-id']);
        unset($row['fa_changed_at']);
        
 
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