<?php
namespace Fharch\Core\Modules\Oeffentlichkeitsarbeit\API;

use \PDO;
use Fharch\Core\Services\TableColumnMetadata;

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class BU_ListRepository {
    private PDO $pdo;
    protected static string $logFile = 'BU_ListRepository_debug.log.txt';
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getBuecher(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM oe_buecher ";
        $where = [];
        $params = [];
        
        switch ($listType) {
            case 'Alle':
            default:
                # $where[] = "mi_name != ''";
                $orderBy = "ORDER BY bu_id";
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
        $bu_id = $row['bu_id']; // ?? 0;
        
        #if ($tabTyp != "Extern") {
            $row['action'] = "<a href='BuchEdit.php?ID={$bu_id}'>Edit</a>";
        #}
        
        $d_path = "../../../../login/AOrd_Verz/Buch/";
        if ($row['bu_bild_1'] != "") {
            $bild1 = $row['bu_bild_1'];
            $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION));
            
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                $row['bu_bild_1'] = "<a href='$d_path$bild1' target='_blank'><img src='$d_path$bild1' alt='$bild1' height='200' ></a>";
            } else {
                $row['bu_bild_1'] = "<a href='$d_path$bild1' target='_blank'>$bild1</a>";
            }
        }
        
        if ($row['bu_bild_2'] != "") {
            $bild2 = $row['bu_bild_2'];
            $ext = strtolower(pathinfo($bild2, PATHINFO_EXTENSION));
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                $row['bu_bild_2'] = "<a href='$d_path$bild2' target='_blank'><img src='$d_path$bild2' alt='$bild2' height='200' ></a>";
            } else {
                $row['bu_bild_2'] = "<a href='$d_path$bild2' target='_blank'>$bild2</a>";
            }
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